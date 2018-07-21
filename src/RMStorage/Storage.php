<?php

namespace App\RMStorage;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Storage implements StorageInterface
{

    private $logger;
    private $client;

    /**
     * Storage constructor.
     * @param LoggerInterface $logger
     * @param string          $rmUrl
     * @param string          $rmAuthUser
     * @param string          $rmAuthPass
     * @param string          $rmToken
     */
    public function __construct(LoggerInterface $logger, string $rmUrl, string $rmAuthUser, string $rmAuthPass, string $rmToken)
    {
        $this->logger = $logger;
        $this->client = new Client(
            [
                'base_uri'        => $rmUrl,
                'headers'         => [
                    'X-Redmine-API-Key' => $rmToken,
                ],
                'auth'            => [$rmAuthUser, $rmAuthPass],
                'timeout'         => 3,
                'connect_timeout' => 3,
                'decode_content'  => 'gzip',
                'http_errors'     => false
            ]
        );
    }

    /**
     * @return Project[]
     */
    public function getProjects(): array
    {
        return [];
        $response = $this->client->request('GET', '/projects.json', ['headers' => ['Content-Type' => 'application/json']]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('RMStorage request /projects.json has invalid response status', [
                'response'    => $response->getBody()->getContents(),
                'status_code' => $response->getStatusCode()
            ]);

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
            ]);

            return [];
        }

        $projects = [];
        foreach ($data['projects'] as $p) {
            foreach (['id', 'name'] as $key) {
                if (!array_key_exists($key, $p)) {
                    $this->logger->error('RMStorage request /projects.json has not key in response', [
                        'response' => $content,
                        'key'      => $key
                    ]);
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
        return [];
        $response = $this->client->request('GET', '/issues.json', [
            'query'   => ['assigned_to_id' => 'me', 'limit' => 100],
            'headers' => ['Content-Type' => 'application/json']
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('RMStorage request /issues.json has invalid response status code', [
                'response'    => $response->getBody()->getContents(),
                'status_code' => $response->getStatusCode()
            ]);

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
            ]);

            return [];
        }

        $issues = [];
        foreach ($data['issues'] as $issue) {

            foreach (['id', 'subject', 'project'] as $key) {
                if (!array_key_exists($key, $issue)) {
                    $this->logger->error('RMStorage request /issues.json has not key in response', [
                        'response' => $content,
                        'key'      => $key
                    ]);
                    continue 2;
                }
            }

            $project = $issue['project'];

            foreach (['id', 'name'] as $key) {
                if (!array_key_exists($key, $project)) {
                    $this->logger->error('RMStorage request /issues.json has not key in response', [
                        'response' => $content,
                        'key'      => 'project.' . $key
                    ]);
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
        return true;
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><time_entry></time_entry>');
        $xml->addChild('issue_id', $timeEntry->getIssue()->getId());
        $xml->addChild('hours', $timeEntry->getHours());

        $response = $this->client->request('POST', '/time_entries.xml', [
            'headers' => ['Content-Type' => 'application/xml'],
            'body'    => $xml->asXML()
        ]);

        if ($response->getStatusCode() !== 201) {
            $this->logger->error('RMStorage request /time_entries.xml has invalid response status', [
                'response'    => $response->getBody()->getContents(),
                'status_code' => $response->getStatusCode()
            ]);

            return false;
        }

        return true;
    }
}