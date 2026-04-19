const club_div = document.querySelector('.club_div');
const putter_div = document.querySelector('.putter_div');
const club_num_input = document.getElementById('clubs_num');
const putter_num_input = document.getElementById('putters_num');
const check_yes = document.getElementById('provide_grips_yes');
const check_no = document.getElementById('provide_grips_no');

function change_select_divs() {
    const club_num = document.getElementById('clubs_num').value;
    const putter_num = document.getElementById('putters_num').value;

    if(check_no.checked) {
        if(club_num > 0) {
            club_div.removeAttribute("hidden")
        } else {
            club_div.setAttribute("hidden", "")
        }
        if(putter_num > 0) {
            putter_div.removeAttribute("hidden")
        } else {
            putter_div.setAttribute("hidden", "")
        }
    }
};

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
};

function generate_club_label() {
    if (check_yes.checked) {
        return;
    }
    club_div.innerHTML = "";
    const club_num = document.getElementById('clubs_num').value;
    const apply_button = document.createElement('button');
    const apply_all = document.createElement('input');

    apply_all.setAttribute('type', 'text');
    apply_all.setAttribute('list', 'club_grip');
    apply_button.textContent = "Apply to All";
    apply_button.setAttribute('type', 'button');
    apply_button.addEventListener('click', function() {
        const option = document.querySelector('#club_grip option[value="' + apply_all.value + '"]');
        const data_id = option ? option.getAttribute('data-id') : null;
        club_div.querySelectorAll('input').forEach(input => {
            if (input.type !== 'hidden') {
                input.value = apply_all.value;
            } else {
                input.value = data_id;
            }
        });
        if (data_id) {
            fetch('../pages/get_price.php?grip_id=' + data_id)
                .then(response => response.json())
                .then(data => {
                    club_div.querySelectorAll('.slot_div').forEach(div => {
                        div.dataset.price = data.price;
                        let price_el = div.querySelector('.slot_price');
                        if (!price_el) {
                            price_el = document.createElement('span');
                            price_el.classList.add('slot_price');
                            div.appendChild(price_el);
                        }
                        price_el.textContent = '$' + parseFloat(data.price).toFixed(2);
                    });
                    update_total();
                });
        }
    });

    const club_heading = document.createElement('p');
    club_heading.textContent = "Club Grips";
    club_div.appendChild(club_heading);

    const apply_label = document.createElement('label');
    apply_label.textContent = "Apply same grip to all clubs:";
    const apply_div = document.createElement('div');
    apply_div.style.marginBottom = "16px";
    apply_div.appendChild(apply_label);
    apply_div.appendChild(apply_all);
    apply_div.appendChild(apply_button);
    club_div.appendChild(apply_div);

    for (let index = 0; index < club_num; index++) {
        const label = document.createElement("label");
        const label_text = document.createTextNode("Club " + (index + 1) + ": ");
        label.appendChild(label_text);

        const id_input = document.createElement("input");
        id_input.setAttribute('name', 'club_grip_id_' + (index + 1));
        id_input.setAttribute('type', 'hidden');

        const input = document.createElement("input");
        input.setAttribute('type', 'text');
        input.setAttribute('list', 'club_grip');
        input.setAttribute('name', 'club_grip_' + (index + 1));
        input.setAttribute('required', '');
        input.addEventListener('change', function() {
            const option = document.querySelector('#club_grip option[value="' + input.value + '"]');
            const data_id = option ? option.getAttribute('data-id') : null;
            id_input.value = data_id;
            fetch('../pages/get_price.php?grip_id=' + data_id)
                .then(response => response.json())
                .then(data => {
                    div.dataset.price = data.price;
                    let price_el = div.querySelector('.slot_price');
                    if (!price_el) {
                        price_el = document.createElement('span');
                        price_el.classList.add('slot_price');
                        div.appendChild(price_el);
                    }
                    price_el.textContent = '$' + parseFloat(data.price).toFixed(2);
                    update_total();
                });
        });

        const div = document.createElement('div');
        div.classList.add('slot_div');
        div.appendChild(label);
        div.appendChild(input);
        div.appendChild(id_input);
        club_div.appendChild(div);
    }
};

function generate_putter_label() {
    if (check_yes.checked) {
        return;
    }
    putter_div.innerHTML = "";
    const putter_num = document.getElementById('putters_num').value;
    const apply_button = document.createElement('button');
    const apply_all = document.createElement("input");

    apply_all.setAttribute('type', 'text');
    apply_all.setAttribute('list', 'putter_grip');
    apply_button.textContent = "Apply to All";
    apply_button.setAttribute('type', 'button');
    apply_button.addEventListener('click', function() {
        const option = document.querySelector('#putter_grip option[value="' + apply_all.value + '"]');
        const data_id = option ? option.getAttribute('data-id') : null;
        putter_div.querySelectorAll('input').forEach(input => {
            if (input.type !== 'hidden') {
                input.value = apply_all.value;
            } else {
                input.value = data_id;
            }
        });
        if (data_id) {
            fetch('../pages/get_price.php?grip_id=' + data_id)
                .then(response => response.json())
                .then(data => {
                    putter_div.querySelectorAll('.slot_div').forEach(div => {
                        div.dataset.price = data.price;
                        let price_el = div.querySelector('.slot_price');
                        if (!price_el) {
                            price_el = document.createElement('span');
                            price_el.classList.add('slot_price');
                            div.appendChild(price_el);
                        }
                        price_el.textContent = '$' + parseFloat(data.price).toFixed(2);
                    });
                    update_total();
                });
        }
    });

    const putter_heading = document.createElement('p');
    putter_heading.textContent = "Putter Grips";
    putter_div.appendChild(putter_heading);

    const apply_label = document.createElement('label');
    apply_label.textContent = "Apply same grip to all putters:";
    const apply_div = document.createElement('div');
    apply_div.style.marginBottom = "16px";
    apply_div.appendChild(apply_label);
    apply_div.appendChild(apply_all);
    apply_div.appendChild(apply_button);
    putter_div.appendChild(apply_div);

    for (let index = 0; index < putter_num; index++) {
        const label = document.createElement("label");
        const label_text = document.createTextNode("Putter " + (index + 1) + ": ");
        label.appendChild(label_text)

        const id_input = document.createElement("input");
        id_input.setAttribute('name', 'putter_grip_id_' + (index + 1));
        id_input.setAttribute('type', 'hidden');

        const input = document.createElement("input");
        input.setAttribute('type', 'text');
        input.setAttribute('list', 'putter_grip');
        input.setAttribute('name', 'putter_grip_' + (index + 1));
        input.setAttribute('required', '');
        input.addEventListener('change', function() {
        const option = document.querySelector('#putter_grip option[value="' + input.value + '"]');
        const data_id = option ? option.getAttribute('data-id') : null;
        id_input.value = data_id;
        fetch('../pages/get_price.php?grip_id=' + data_id)
            .then(response => response.json())
            .then(data => {
                div.dataset.price = data.price;
                let price_el = div.querySelector('.slot_price');
                if (!price_el) {
                    price_el = document.createElement('span');
                    price_el.classList.add('slot_price');
                    div.appendChild(price_el);
                }
                price_el.textContent = '$' + parseFloat(data.price).toFixed(2);
                update_total();
            });
        });

        const div = document.createElement('div');
        div.classList.add('slot_div');
        div.appendChild(label);
        div.appendChild(input);
        div.appendChild(id_input);
        putter_div.appendChild(div);
    }
};

check_no.addEventListener('change', (event) => {
    change_select_divs();
    generate_club_label();
    generate_putter_label();
    update_total();
});

check_yes.addEventListener('change', (event) => {
    club_div.setAttribute("hidden", "")
    club_div.innerHTML = "";
    putter_div.setAttribute("hidden", "")
    putter_div.innerHTML = "";
    update_total();
});

club_num_input.addEventListener('input', (event) => {
    change_select_divs();
    generate_club_label();
    update_total();
});

putter_num_input.addEventListener('input', (event) => {
    change_select_divs();
    generate_putter_label();
    update_total();
});

