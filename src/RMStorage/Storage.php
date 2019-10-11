<?php

namespace App\RMStorage;

use App\Repository\ProjectRepository;
use App\Repository\TickerRepository;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Storage implements StorageInterface
{
    private $logger;
    private $client;
    private $rmId;
    private $projectRepository;
    private $tickerRepository;

    public function __construct(
        LoggerInterface $logger,
        ProjectRepository $projectRepository,
        TickerRepository $tickerRepository,
        string $rmUrl,
        string $rmAuthUser,
        string $rmAuthPass,
        string $rmToken,
        int $rmId,
        int $rmTimeoutSec,
        int $rmConnTimeoutSec
    )
    {
        $config = [
            'base_uri'        => $rmUrl,
            'headers'         => [
                'X-Redmine-API-Key' => $rmToken
            ],
            'timeout'         => $rmTimeoutSec,
            'connect_timeout' => $rmConnTimeoutSec,
            'decode_content'  => 'gzip',
            'http_errors'     => false
        ];

        if (!empty($rmAuthUser) || !empty($rmAuthPass)) {
            $config['auth'] = [$rmAuthUser, $rmAuthPass];
        }

        $this->logger            = $logger;
        $this->rmId              = $rmId;
        $this->client            = new Client($config);
        $this->projectRepository = $projectRepository;
        $this->tickerRepository  = $tickerRepository;
    }

    /**
     * @return Project[]
     */
    public function getProjects(): array
    {
        $this->logger->debug('HTTP Request', ['method' => 'GET', 'uri' => '/projects.json']);
        $response = $this->client->request('GET', '/projects.json', ['headers' => ['Content-Type' => 'application/json']]);
        $this->logger->debug('HTTP Response', ['method' => 'GET', 'uri' => '/projects.json', 'responseStatus' => $response->getStatusCode()]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('RMStorage request /projects.json has invalid response status', [
                                                                                                   'response'    => $response->getBody()->getContents(),
                                                                                                   'status_code' => $response->getStatusCode()
                                                                                               ]
            );

            return [];
        }

        $content = $response->getBody()->getContents();
        $data    = json_decode($content, true);

        if (!is_array($data)) {
            $this->logger->error('RMStorage request /projects.json has invalid response content', ['response' => $content]);

            return [];
        }

        if (!array_key_exists('projects', $data)) {
            $this->logger->error('RMStorage request /projects.json has not key in response', [
                                                                                               'response' => $content,
                                                                                               'key'      => 'projects'
                                                                                           ]
            );

            return [];
        }

        $projects = [];
        foreach ($data['projects'] as $p) {
            foreach (['id', 'name'] as $key) {
                if (!array_key_exists($key, $p)) {
                    $this->logger->error('RMStorage request /projects.json has not key in response', [
                                                                                                       'response' => $content,
                                                                                                       'key'      => $key
                                                                                                   ]
                    );
                    continue 2;
                }
            }

            $id   = (int)$p['id'];
            $name = $p['name'];

            $projects[] = new Project($id, $name);
        }

        return $projects;
    }

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        $this->logger->debug('HTTP Request', ['method' => 'GET', 'uri' => '/issues.json']);
        $response = $this->client->request('GET', '/issues.json', [
                                                    'query'   => ['assigned_to_id' => 'me', 'limit' => 100],
                                                    'headers' => ['Content-Type' => 'application/json']
                                                ]
        );
        $this->logger->debug('HTTP Response', ['method' => 'GET', 'uri' => '/issues.json', 'responseStatus' => $response->getStatusCode()]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('RMStorage request /issues.json has invalid response status code', [
                                                                                                      'response'    => $response->getBody()->getContents(),
                                                                                                      'status_code' => $response->getStatusCode()
                                                                                                  ]
            );

            return [];
        }

        $content = $response->getBody()->getContents();
        $data    = json_decode($content, true);

        if (!is_array($data)) {
            $this->logger->error('RMStorage request /issues.json has invalid response content', ['response' => $content]);

            return [];
        }

        if (!array_key_exists('issues', $data)) {
            $this->logger->error('RMStorage request /issues.json has not key in response', [
                                                                                             'response' => $content,
                                                                                             'key'      => 'issues'
                                                                                         ]
            );

            return [];
        }

        $issues = [];
        foreach ($data['issues'] as $issue) {
            foreach (['id', 'subject', 'project'] as $key) {
                if (!array_key_exists($key, $issue)) {
                    $this->logger->error('RMStorage request /issues.json has not key in response', [
                                                                                                     'response' => $content,
                                                                                                     'key'      => $key
                                                                                                 ]
                    );
                    continue 2;
                }
            }

            $project = $issue['project'];

            foreach (['id', 'name'] as $key) {
                if (!array_key_exists($key, $project)) {
                    $this->logger->error('RMStorage request /issues.json has not key in response', [
                                                                                                     'response' => $content,
                                                                                                     'key'      => 'project.' . $key
                                                                                                 ]
                    );
                    continue 2;
                }
            }

            $projectId    = (int)$project['id'];
            $projectName  = $project['name'];
            $issueId      = (int)$issue['id'];
            $issueSubject = $issue['subject'];

            $newIssue = new Issue($issueId, $issueSubject);
            $newIssue->setProject(new Project($projectId, $projectName));
            $issues[] = $newIssue;
        }

        return $issues;
    }

    public function createTimeEntry(TimeEntry $timeEntry): bool
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><time_entry></time_entry>');
        $xml->addChild('issue_id', $timeEntry->getIssue()->getId());
        $xml->addChild('hours', $timeEntry->getHours());

        $this->logger->debug('HTTP Request', [
            'method'   => 'POST',
            'uri'      => '/time_entries.xml',
            'issue_id' => $timeEntry->getIssue()->getId(),
            'hours'    => $timeEntry->getHours()
        ]
        );

        $response = $this->client->request('POST', '/time_entries.xml', [
                                                     'headers' => ['Content-Type' => 'application/xml'],
                                                     'body'    => $xml->asXML()
                                                 ]
        );

        $this->logger->debug('HTTP Response', ['method' => 'POST', 'uri' => '/time_entries.xml', 'responseStatus' => $response->getStatusCode()]);

        if ($response->getStatusCode() !== 201) {
            $this->logger->error('RMStorage request /time_entries.xml has invalid response status', [
                                                                                                      'response'    => $response->getBody()->getContents(),
                                                                                                      'status_code' => $response->getStatusCode()
                                                                                                  ]
            );

            return false;
        }

        return true;
    }

    public function createIssue(Issue $issue): int
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
        $xml->addChild('project_id', $issue->getProject()->getId());
        $xml->addChild('subject', $issue->getSubject());
        $xml->addChild('assigned_to_id', $this->rmId);

        $this->logger->debug('HTTP Request', [
            'method'         => 'POST',
            'uri'            => '/issues.xml',
            'project_id'     => $issue->getProject()->getId(),
            'subject'        => $issue->getSubject(),
            'assigned_to_id' => $this->rmId
        ]
        );
        $response = $this->client->request('POST', '/issues.xml', [
                                                     'headers' => ['Content-Type' => 'application/xml'],
                                                     'body'    => $xml->asXML()
                                                 ]
        );
        $this->logger->debug('HTTP Response', ['method' => 'POST', 'uri' => '/issues.xml', 'responseStatus' => $response->getStatusCode()]);

        $xmlResponse = new \SimpleXMLElement($response->getBody()->getContents());

        if ($response->getStatusCode() !== 201) {
            $this->logger->error('RMStorage request /issues.xml has invalid response status', [
                                                                                                'response'    => $response->getBody()->getContents(),
                                                                                                'status_code' => $response->getStatusCode()
                                                                                            ]
            );

            return 0;
        }

        return (int)$xmlResponse->id;
    }
}