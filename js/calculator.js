const club_brand_div = document.querySelector('.club_brand_div');
const putter_brand_div = document.querySelector('.putter_brand_div');
const check_yes = document.getElementById('provide_grips_yes');
const check_no = document.getElementById('provide_grips_no');
const calc_button = document.getElementById('calc_button');
const club_num_input = document.getElementById('clubs_num');
const putter_num_input = document.getElementById('putters_num');

function change_select_divs() {
    const club_num = document.getElementById('clubs_num').value;
    const putter_num = document.getElementById('putters_num').value;

    if(check_no.checked) {
        if(club_num > 0) {
            club_brand_div.removeAttribute("hidden")
        } else {
            club_brand_div.setAttribute("hidden", "")
        }
        if(putter_num > 0) {
            putter_brand_div.removeAttribute("hidden")
        } else {
            putter_brand_div.setAttribute("hidden", "")
        }
    }
};


check_no.addEventListener('change', (event) => {
    change_select_divs();
});

check_yes.addEventListener('change', (event) => {
    club_brand_div.setAttribute("hidden", "")
    putter_brand_div.setAttribute("hidden", "")
});

club_num_input.addEventListener('input', (event) => {
    change_select_divs();
});

putter_num_input.addEventListener('input', (event) => {
    change_select_divs();
});

calc_button.addEventListener('click', function() {
    const club_num = parseFloat(document.getElementById('clubs_num').value);
    const putter_num = parseFloat(document.getElementById('putters_num').value);
    const result = document.getElementById('result');
    const club_and_putter_total = club_num + putter_num;
    const labor_cost = 5.0;
    const labor = labor_cost * club_and_putter_total;
    let total = 0.0;

    if (club_and_putter_total === 0) {
        result.innerHTML = `<p>Please enter at least one club.</p>`;
        return;
    }

    if(check_yes.checked) {
        total = club_and_putter_total * labor_cost;
        result.innerHTML = `<p>Estimated Price: $${total.toFixed(2)}</p>`;
    } else {
        const club_grip_value = document.getElementById('club_grip_brand').value;
        const putter_grip_value = document.getElementById('putter_grip_brand').value;
        const club_type = grip_prices.find(item => item.brand === club_grip_value && item.club_type === 'swinging');
        const putter_type = grip_prices.find(item => item.brand === putter_grip_value && item.club_type === 'putter');
        const club_min_price = club_type.min_price;
        const club_max_price = club_type.max_price;
        const putter_min_price = putter_type.min_price;
        const putter_max_price = putter_type.max_price;

        const min_total = labor + (club_num * club_min_price) + (putter_num * putter_min_price);
        const max_total = labor + (club_num * club_max_price) + (putter_num * putter_max_price);

        result.innerHTML = `<p>Estimated Price: $${min_total.toFixed(2)} - $${max_total.toFixed(2)}</p>`;
    }
});