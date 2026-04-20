const apply_all_clubs_btn = document.getElementById('apply_all_clubs_btn');
const apply_all_clubs_input = document.getElementById('apply_all_clubs_input');
const apply_all_clubs_id = document.getElementById('apply_all_clubs_id');

const apply_all_putters_btn = document.getElementById('apply_all_putters_btn');
const apply_all_putters_input = document.getElementById('apply_all_putters_input');
const apply_all_putters_id = document.getElementById('apply_all_putters_id');

const club_div = document.querySelector('.club_div');
const putter_div = document.querySelector('.putter_div');

function updateSlotPrice(slotDiv, gripId) {
    if (!slotDiv || !gripId) return;

    fetch('../pages/get_price.php?grip_id=' + encodeURIComponent(gripId))
        .then(response => response.json())
        .then(data => {
            slotDiv.dataset.price = data.price;

            const priceEl = slotDiv.querySelector('.slot_price');
            if (priceEl) {
                const skuEl = priceEl.querySelector('.slot_sku');
                priceEl.innerHTML = '$' + parseFloat(data.price).toFixed(2);

                if (skuEl) {
                    priceEl.appendChild(skuEl);
                }
            }
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

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.grip-search').forEach(setupGripSearch);

    if (apply_all_clubs_btn && apply_all_clubs_input && apply_all_clubs_id && club_div) {
        apply_all_clubs_btn.addEventListener('click', function () {
            const gripId = parseInt(apply_all_clubs_id.value, 10);

            if (!gripId) return;

            applyGripToAll(club_div, apply_all_clubs_input.value, gripId);
        });
    }

    if (apply_all_putters_btn && apply_all_putters_input && apply_all_putters_id && putter_div) {
        apply_all_putters_btn.addEventListener('click', function () {
            const gripId = parseInt(apply_all_putters_id.value, 10);

            if (!gripId) return;

            applyGripToAll(putter_div, apply_all_putters_input.value, gripId);
        });
    }
});