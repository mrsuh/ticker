'use strict';

let init = function ({urlStopTicker, urlStartTicker, urlCreateTicker}) {
    let cards = document.querySelectorAll('.cards .card');
    let cardsParenElem = document.querySelector('.cards');
    let createCardElem = document.querySelector('.card-create');
    let cardCreateInput = document.getElementById('js-input-create');
    let cardCreateBtn = document.getElementById('js-btn-create');
    let cardTemplateElem = document.querySelector('.card-templates .card');
    let inputSearch = document.getElementById('js-input-search');
    let btnStop = document.getElementById('js-btn-stop');
    let loadingBar = document.querySelector('.loading');
    inputSearch.focus();
    let progressBarCounter = 0;

    let sendStopTickerRequest = function (callback) {
        showProgressBar();

        let xhr = new XMLHttpRequest();

        xhr.open('PUT', urlStopTicker, true);

        xhr.send();

        xhr.onreadystatechange = function () {
            if (this.readyState !== 4) return;

            hideProgressBar();

            if (this.status !== 200) {
                callback && callback(false, this.responseText);

                return false;
            }

            callback && callback(true, this.responseText);
        }
    };

    let sendStartTickerRequest = function (tickerId, callback) {
        showProgressBar();

        let xhr = new XMLHttpRequest();

        xhr.open('PUT', urlStartTicker.replace('ticker_id', tickerId), true);

        xhr.send();

        xhr.onreadystatechange = function () {
            if (this.readyState !== 4) return;

            hideProgressBar();

            if (this.status !== 200) {
                callback && callback(false, this.responseText);

                return false;
            }

            callback && callback(true, this.responseText);
        }
    };

    let sendCreateTickerRequest = function (name, callback) {
        showProgressBar();

        let xhr = new XMLHttpRequest();

        xhr.open('POST', urlCreateTicker, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        let body = 'name=' + encodeURIComponent(name);
        xhr.send(body);

        xhr.onreadystatechange = function () {
            if (this.readyState !== 4) return;

            hideProgressBar();

            if (this.status !== 200) {
                callback && callback(false, this.responseText);

                return false;
            }

            callback && callback(true, this.responseText);
        }
    };

    let disableCard = function (card) {
        let input = card.querySelector('input');
        if (input) {
            input.setAttribute('disabled', 'disabled');
        }
        let btn = card.querySelector('.btn');
        if (btn) {
            btn.setAttribute('disabled', 'disabled');
        }
        card.addClass('disabled');
    };

    let enableCard = function (card) {
        let input = card.querySelector('input');
        if (input) {
            input.removeAttribute('disabled');
        }
        let btn = card.querySelector('.btn');
        if (btn) {
            btn.removeAttribute('disabled');
        }
        card.removeClass('disabled');
    };

    let disableBtn = function (btn) {
        btn.setAttribute('disabled', 'disabled');
        btn.addClass('disabled');
    };

    let enableBtn = function (btn) {
        btn.removeAttribute('disabled');
        btn.removeClass('disabled');
    };

    let active = function (card) {
        let id = card.getAttribute('data-id');
        disableCard(card);
        sendStartTickerRequest(id, function (success, data) {
            if (!success) {
                showAlert('Start ticker error: ' + data);
                enableCard(card);

                return false;
            }

            disactiveCards();
            enableCard(card);
            activeCard(card);
            sortCards();
            scrollToTop();
            showCards();

            inputSearch.value = '';
        });
    };

    let showCards = function () {
        for (let i = 0; i < cards.length; i++) {
            let card = cards[i];
            card.removeClass('hide');
        }
    };

    let showCard = function (card) {
        card.removeClass('hide');
    };

    let hideCard = function (card) {
        card.addClass('hide');
    };

    let activeCard = function (card) {
        let background = card.getAttribute('data-background');
        card.setAttribute('data-last-tick', (Math.floor(Date.now() / 1000)));
        card.removeClass('border-' + background);
        card.addClass('text-white');
        card.addClass('active');
        card.addClass('bg-' + background);

        let cardBody = document.createElement('div');
        cardBody.addClass('card-body');
        cardBody.innerText = '00:00:00';
        card.appendChild(cardBody);
        timer();
    };

    let disactiveCard = function (card) {
        let background = card.getAttribute('data-background');
        card.addClass('border-' + background);
        card.removeClass('text-white');
        card.removeClass('bg-' + background);
        card.removeClass('active');

        let cardBody = card.querySelector('.card-body');
        if (cardBody) {
            card.removeChild(cardBody);
        }
    };

    let disactiveCards = function () {
        for (let i = 0; i < cards.length; i++) {
            let card = cards[i];

            if (isCreateCard(card)) {
                continue;
            }

            disactiveCard(card);
        }
    };

    let sortCards = function () {
        let cardsSlice = Array.prototype.slice.call(document.querySelectorAll('.cards .card'));
        cardsSlice.sort(function (a, b) {
            let aTick = parseInt(a.getAttribute('data-last-tick'));
            let bTick = parseInt(b.getAttribute('data-last-tick'));
            return aTick > bTick;
        });

        cardsSlice.forEach(function (item, idx) {
            if (idx > 0) {
                item.parentNode.insertBefore(item, cardsSlice[idx - 1]);
            }
        });

        let cards = document.querySelector('.cards');
        let activeCard = document.querySelector('.card.active');
        cards.insertBefore(createCardElem, cards.firstChild);
        cards.insertBefore(activeCard, cards.firstChild);
    };

    let scrollToTop = function () {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    };

    let isCreateCard = function (card) {
        return card.hasClass('card-create');
    };

    let timer = function () {
        let activeCard = document.querySelector('.card.active');

        if (!activeCard) {
            return;
        }

        let cardBody = activeCard.querySelector('.card-body');

        if (!cardBody) {
            return;
        }

        let tickDateTime = activeCard.getAttribute('data-last-tick');
        let nowDateTime = (Math.floor(Date.now() / 1000));

        let diffSeconds = nowDateTime - tickDateTime;

        let hours = Math.floor(diffSeconds / (60 * 60));
        let minutes = Math.floor((diffSeconds - hours * (60 * 60)) / (60));
        let seconds = (diffSeconds - hours * (60 * 60) - minutes * (60));

        cardBody.innerHTML = ('0' + hours).substr(-2) + ':' + ('0' + minutes).substr(-2) + ':' + ('0' + seconds).substr(-2);
    };

    let isSubstr = function (queryString, text) {

        if (0 === queryString.length) {
            return false;
        }

        text = text.toLowerCase();
        queryString = queryString.toLowerCase();

        if (-1 !== text.indexOf(queryString)) {
            return true;
        }

        let words = [
            {en: 'q', ru: 'й'},
            {en: 'w', ru: 'ц'},
            {en: 'e', ru: 'у'},
            {en: 'r', ru: 'к'},
            {en: 't', ru: 'е'},
            {en: 'y', ru: 'н'},
            {en: 'u', ru: 'г'},
            {en: 'i', ru: 'ш'},
            {en: 'o', ru: 'щ'},
            {en: 'p', ru: 'з'},
            {en: 'a', ru: 'ф'},
            {en: 's', ru: 'ы'},
            {en: 'd', ru: 'в'},
            {en: 'f', ru: 'а'},
            {en: 'g', ru: 'п'},
            {en: 'h', ru: 'р'},
            {en: 'j', ru: 'о'},
            {en: 'k', ru: 'л'},
            {en: 'l', ru: 'д'},
            {en: 'z', ru: 'я'},
            {en: 'x', ru: 'ч'},
            {en: 'c', ru: 'с'},
            {en: 'v', ru: 'м'},
            {en: 'b', ru: 'и'},
            {en: 'n', ru: 'т'},
            {en: 'm', ru: 'ь'}
        ];

        let regexpEn = new RegExp(/[a-z]/i);
        let isEn = regexpEn.test(queryString);

        let queryStringTr = queryString;
        for (let i = 0; i < words.length; i++) {
            let word = words[i];
            if (isEn) {
                queryStringTr = queryStringTr.replace(word.en, word.ru);
            } else {
                queryStringTr = queryStringTr.replace(word.ru, word.en);
            }
        }

        return -1 !== text.indexOf(queryStringTr);
    };

    let createTicker = function () {
        let name = cardCreateInput.value;
        if (name.length === 0) {
            return false;
        }

        disableCard(createCardElem);

        sendCreateTickerRequest(name, function (success, data) {
            if (!success) {
                showAlert('Create ticker error: ' + data);
                enableCard(createCardElem);
                cardCreateInput.value = '';
                return false;
            }

            let json = JSON.parse(data);

            createCard(json.data);
            enableCard(createCardElem);
            cardCreateInput.value = '';
        });
    };

    let createCard = function ({id, rmId, name, lastTickAt}) {
        let newCard = cardTemplateElem.cloneNode(true);
        newCard.setAttribute('data-id', id);
        newCard.setAttribute('data-last-tick', lastTickAt);
        newCard.querySelector('.card-header').innerText = '#' + rmId + ' ' + name;
        cardsParenElem.appendChild(newCard);
        disactiveCards();
        showCard(newCard);
        activeCard(newCard);
        sortCards();
        scrollToTop();
        showCards();
    };

    let showProgressBar = function () {
        progressBarCounter++;
        loadingBar.removeClass('hide');
    };

    let hideProgressBar = function () {
        progressBarCounter--;
        if (progressBarCounter <= 0) {
            loadingBar.addClass('hide');
        }
    };

    let projectSelect = document.querySelector('.js-select-project');
    projectSelect.addEventListener('change', function () {
        let project = projectSelect.options[projectSelect.selectedIndex].getAttribute('value');
        window.location.href = window.location.pathname + '?project=' + project;
    });

    for (let i = 0; i < cards.length; i++) {
        let card = cards[i];
        card.addEventListener('click', function (e) {

            e.stopPropagation();
            e.preventDefault();

            if (isCreateCard(this)) {
                return false;
            }

            active(this);
        });
    }

    timer();
    setInterval(timer, 1000);

    inputSearch.addEventListener('keyup', function (e) {
        let queryString = this.value.toLowerCase();

        if (queryString.length === 0) {
            showCards();
            return false;
        }

        for (let i = 0; i < cards.length; i++) {
            let card = cards[i];
            let text = card.querySelector('.card-header').innerText;

            if (isCreateCard(card)) {
                showCard(card);
                continue;
            }

            if (isSubstr(queryString, text.toLowerCase())) {
                showCard(card);
            } else {
                hideCard(card);
            }
        }
    });

    btnStop.addEventListener('click', function (e) {
        e.preventDefault();
        disableBtn(btnStop);
        sendStopTickerRequest(function (success, data) {
            if (!success) {
                showAlert('Stop ticker error: ' + data);
                hideProgressBar();
                enableBtn(btnStop);
                return;
            }

            disactiveCards();
            hideProgressBar();
            enableBtn(btnStop);
        });
    });

    cardCreateInput.addEventListener('keypress', function () {
        if (event.keyCode === 13) {
            event.preventDefault();
            createTicker();
        }
    });

    cardCreateBtn.addEventListener('click', function (e) {
        createTicker();
    });
};
