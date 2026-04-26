if (new URLSearchParams(window.location.search).get('status') === 'error') {
    const message = document.getElementById('toast-message');
    const toast_div = document.getElementById('toast');
    message.textContent = "Please complete the captcha before submitting.";
    toast_div.removeAttribute('hidden');
    setTimeout(() => {
        toast_div.setAttribute('hidden', '');
    }, 10000);
}

const club_div = document.querySelector('.club_div');
const putter_div = document.querySelector('.putter_div');
const club_num_input = document.getElementById('clubs_num');
const putter_num_input = document.getElementById('putters_num');
const check_yes = document.getElementById('own_grips_yes');
const check_no = document.getElementById('own_grips_no');

function change_select_divs() {
    const club_num = parseInt(club_num_input.value) || 0;
    const putter_num = parseInt(putter_num_input.value) || 0;

    if (check_no.checked) {
        if (club_num > 0) {
            club_div.removeAttribute('hidden');
        } else {
            club_div.setAttribute('hidden', '');
        }

        if (putter_num > 0) {
            putter_div.removeAttribute('hidden');
        } else {
            putter_div.setAttribute('hidden', '');
        }
    } else {
        club_div.setAttribute('hidden', '');
        putter_div.setAttribute('hidden', '');
    }
}

function update_total() {
    let total_price = 0.0;
    const labor_per_club = parseFloat(document.getElementById('labor_per_club').value);

    if (check_yes.checked) {
        const clubs = parseInt(club_num_input.value) || 0;
        const putters = parseInt(putter_num_input.value) || 0;
        total_price = labor_per_club * (clubs + putters);
    } else {
        club_div.querySelectorAll('.slot_div').forEach(div => {
            if (div.dataset.price) {
                total_price += parseFloat(div.dataset.price);
            }
        });

        putter_div.querySelectorAll('.slot_div').forEach(div => {
            if (div.dataset.price) {
                total_price += parseFloat(div.dataset.price);
            }
        });
    }

    const total_el = document.getElementById('order_total');
    total_el.style.display = total_price > 0 ? 'block' : 'none';
    total_el.textContent = 'Estimated Total: $' + total_price.toFixed(2);
}

function updateSlotPrice(slotDiv, gripId) {
    if (!slotDiv || !gripId) return;

    fetch('../pages/get_price.php?grip_id=' + encodeURIComponent(gripId))
        .then(response => response.json())
        .then(data => {
            slotDiv.dataset.price = data.price;

            let priceEl = slotDiv.querySelector('.slot_price');
            if (!priceEl) {
                priceEl = document.createElement('span');
                priceEl.classList.add('slot_price');
                slotDiv.appendChild(priceEl);
            }

            priceEl.textContent = '$' + parseFloat(data.price).toFixed(2);
            update_total();
        })
        .catch(error => {
            console.error('Error fetching price:', error);
        });
}

function setupGripSearch(container) {
    const type = container.dataset.type;
    const input = container.querySelector('.grip-search-input');
    const hiddenId = container.querySelector('.grip-search-id');
    const results = container.querySelector('.grip-search-results');

    if (!input || !hiddenId || !results) return;

    const options = type === 'swing' ? swingGripOptions : putterGripOptions;

    function renderResults(query) {
        const q = query.trim().toLowerCase();
        results.innerHTML = '';

        let matches = options;

        if (q) {
            const tokens = q.split(/\s+/).filter(t => t.length > 0);
            matches = options.filter(option => {
                const label = option.label.toLowerCase();
                return tokens.every(token => label.includes(token));
            });
        }

        matches = matches.slice(0, 25);

        if (matches.length === 0) {
            results.style.display = 'none';
            return;
        }

        matches.forEach(option => {
            const item = document.createElement('div');
            item.className = 'grip-search-result';
            item.textContent = option.label;

            item.addEventListener('mousedown', function (event) {
                event.preventDefault();

                input.value = option.label;
                hiddenId.value = option.id;
                results.innerHTML = '';
                results.style.display = 'none';

                const slotDiv = container.closest('.slot_div');
                if (slotDiv) {
                    updateSlotPrice(slotDiv, option.id);
                }
            });

            results.appendChild(item);
        });

        results.style.display = 'block';
    }

    input.addEventListener('input', function () {
        hiddenId.value = '';
        renderResults(input.value);
    });

    input.addEventListener('focus', function () {
        renderResults(input.value);
    });

    input.addEventListener('blur', function () {
        setTimeout(() => {
            results.style.display = 'none';
        }, 150);
    });
}

function applyGripToAll(sectionDiv, gripLabel, gripId) {
    if (!sectionDiv || !gripId) return;

    sectionDiv.querySelectorAll('.slot_div').forEach(slotDiv => {
        const textInput = slotDiv.querySelector('.grip-search-input');
        const hiddenInput = slotDiv.querySelector('.grip-search-id');

        if (textInput) {
            textInput.value = gripLabel;
        }

        if (hiddenInput) {
            hiddenInput.value = gripId;
        }

        updateSlotPrice(slotDiv, gripId);
    });
}

function createApplyAllBlock(type, labelText, inputId, hiddenId, buttonId) {
    const wrapper = document.createElement('div');
    wrapper.classList.add('apply-all-div');

    const label = document.createElement('label');
    label.textContent = labelText;

    const searchWrapper = document.createElement('div');
    searchWrapper.classList.add('grip-search');
    searchWrapper.dataset.type = type;

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'grip-search-input';
    input.id = inputId;
    input.autocomplete = 'off';
    input.placeholder = type === 'swing' ? 'Search swing grips...' : 'Search putter grips...';

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.className = 'grip-search-id';
    hidden.id = hiddenId;

    const results = document.createElement('div');
    results.className = 'grip-search-results';

    const button = document.createElement('button');
    button.type = 'button';
    button.id = buttonId;
    button.textContent = 'Apply to All';

    searchWrapper.appendChild(input);
    searchWrapper.appendChild(hidden);
    searchWrapper.appendChild(results);

    wrapper.appendChild(label);
    wrapper.appendChild(searchWrapper);
    wrapper.appendChild(button);

    setupGripSearch(searchWrapper);

    return { wrapper, input, hidden, button };
}

function generate_club_label() {
    club_div.innerHTML = '';

    if (check_yes.checked) {
        return;
    }

    const club_num = parseInt(club_num_input.value) || 0;
    if (club_num <= 0) return;

    const club_heading = document.createElement('p');
    club_heading.textContent = 'Club Grips';
    club_div.appendChild(club_heading);

    const applyAll = createApplyAllBlock(
        'swing',
        'Apply same grip to all clubs:',
        'apply_all_clubs_input',
        'apply_all_clubs_id',
        'apply_all_clubs_btn'
    );

    applyAll.button.addEventListener('click', function () {
        const gripId = parseInt(applyAll.hidden.value, 10);
        if (!gripId) return;

        applyGripToAll(club_div, applyAll.input.value, gripId);
    });

    club_div.appendChild(applyAll.wrapper);

    for (let index = 0; index < club_num; index++) {
        const div = document.createElement('div');
        div.classList.add('slot_div');

        const label = document.createElement('label');
        label.textContent = 'Club ' + (index + 1) + ':';

        const searchWrapper = document.createElement('div');
        searchWrapper.classList.add('grip-search');
        searchWrapper.dataset.type = 'swing';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'grip-search-input';
        input.name = 'club_grip_' + (index + 1);
        input.autocomplete = 'off';
        input.placeholder = 'Search swing grips...';
        input.required = true;

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.className = 'grip-search-id';
        hidden.name = 'club_grip_id_' + (index + 1);

        const results = document.createElement('div');
        results.className = 'grip-search-results';

        const price = document.createElement('span');
        price.className = 'slot_price';

        searchWrapper.appendChild(input);
        searchWrapper.appendChild(hidden);
        searchWrapper.appendChild(results);

        div.appendChild(label);
        div.appendChild(searchWrapper);
        div.appendChild(price);

        club_div.appendChild(div);

        setupGripSearch(searchWrapper);
    }
}

function generate_putter_label() {
    putter_div.innerHTML = '';

    if (check_yes.checked) {
        return;
    }

    const putter_num = parseInt(putter_num_input.value) || 0;
    if (putter_num <= 0) return;

    const putter_heading = document.createElement('p');
    putter_heading.textContent = 'Putter Grips';
    putter_div.appendChild(putter_heading);

    const applyAll = createApplyAllBlock(
        'putter',
        'Apply same grip to all putters:',
        'apply_all_putters_input',
        'apply_all_putters_id',
        'apply_all_putters_btn'
    );

    applyAll.button.addEventListener('click', function () {
        const gripId = parseInt(applyAll.hidden.value, 10);
        if (!gripId) return;

        applyGripToAll(putter_div, applyAll.input.value, gripId);
    });

    putter_div.appendChild(applyAll.wrapper);

    for (let index = 0; index < putter_num; index++) {
        const div = document.createElement('div');
        div.classList.add('slot_div');

        const label = document.createElement('label');
        label.textContent = 'Putter ' + (index + 1) + ':';

        const searchWrapper = document.createElement('div');
        searchWrapper.classList.add('grip-search');
        searchWrapper.dataset.type = 'putter';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'grip-search-input';
        input.name = 'putter_grip_' + (index + 1);
        input.autocomplete = 'off';
        input.placeholder = 'Search putter grips...';
        input.required = true;

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.className = 'grip-search-id';
        hidden.name = 'putter_grip_id_' + (index + 1);

        const results = document.createElement('div');
        results.className = 'grip-search-results';

        const price = document.createElement('span');
        price.className = 'slot_price';

        searchWrapper.appendChild(input);
        searchWrapper.appendChild(hidden);
        searchWrapper.appendChild(results);

        div.appendChild(label);
        div.appendChild(searchWrapper);
        div.appendChild(price);

        putter_div.appendChild(div);

        setupGripSearch(searchWrapper);
    }
}

check_no.addEventListener('change', () => {
    change_select_divs();
    generate_club_label();
    generate_putter_label();
    update_total();
});

check_yes.addEventListener('change', () => {
    club_div.setAttribute('hidden', '');
    club_div.innerHTML = '';
    putter_div.setAttribute('hidden', '');
    putter_div.innerHTML = '';
    update_total();
});

club_num_input.addEventListener('input', () => {
    change_select_divs();
    generate_club_label();
    generate_putter_label();
    update_total();
});

putter_num_input.addEventListener('input', () => {
    change_select_divs();
    generate_club_label();
    generate_putter_label();
    update_total();
});

change_select_divs();
generate_club_label();
generate_putter_label();
update_total();