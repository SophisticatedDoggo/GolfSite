const apply_all_clubs_btn = document.getElementById('apply_all_clubs_btn');
const apply_all_clubs_input = document.getElementById('apply_all_clubs_input');
const apply_all_putters_btn = document.getElementById('apply_all_putters_btn');
const apply_all_putters_input = document.getElementById('apply_all_putters_input');
const club_div = document.querySelector('.club_div');
const putter_div = document.querySelector('.putter_div');

apply_all_clubs_btn.addEventListener('click', function() {
    const option = document.querySelector('#club_grip option[value="' + apply_all_clubs_input.value + '"]');
    const data_id = option ? option.getAttribute('data-id') : null;

    if (data_id) {
        club_div.querySelectorAll('.slot_div').forEach(div => {
            div.querySelector('input[type="text"]').value = apply_all_clubs_input.value;
            div.querySelector('input[type="hidden"]').value = data_id;
        });

        fetch('../pages/get_price.php?grip_id=' + data_id)
            .then(response => response.json())
            .then(data => {
                club_div.querySelectorAll('.slot_div').forEach(div => {
                    div.dataset.price = data.price;
                    div.querySelector('.slot_price').textContent = '$' + parseFloat(data.price).toFixed(2);
                });
            });
    }
});

apply_all_putters_btn.addEventListener('click', function() {
    const option = document.querySelector('#putter_grip option[value="' + apply_all_putters_input.value + '"]');
    const data_id = option ? option.getAttribute('data-id') : null;

    if (data_id) {
        putter_div.querySelectorAll('.slot_div').forEach(div => {
            div.querySelector('input[type="text"]').value = apply_all_putters_input.value;
            div.querySelector('input[type="hidden"]').value = data_id;
        });

        fetch('../pages/get_price.php?grip_id=' + data_id)
            .then(response => response.json())
            .then(data => {
                putter_div.querySelectorAll('.slot_div').forEach(div => {
                    div.dataset.price = data.price;
                    div.querySelector('.slot_price').textContent = '$' + parseFloat(data.price).toFixed(2);
                });
            });
    }
});

function attach_slot_listeners(container, datalist_id) {
    container.querySelectorAll('.slot_div').forEach(div => {
        const text_input = div.querySelector('input[type="text"]');
        const hidden_input = div.querySelector('input[type="hidden"]');

        text_input.addEventListener('change', function() {
            const option = document.querySelector('#' + datalist_id + ' option[value="' + text_input.value + '"]');
            const data_id = option ? option.getAttribute('data-id') : null;

            if (data_id) {
                hidden_input.value = data_id;

                fetch('../pages/get_price.php?grip_id=' + data_id)
                    .then(response => response.json())
                    .then(data => {
                        div.dataset.price = data.price;
                        div.querySelector('.slot_price').textContent = '$' + parseFloat(data.price).toFixed(2);
                    });
            }
        });
    });
}

attach_slot_listeners(club_div, 'club_grip');
attach_slot_listeners(putter_div, 'putter_grip');
