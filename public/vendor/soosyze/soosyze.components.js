/*!
 * Soosyze CSS 1.0.0
 *
 * Released under the MIT license
 * https://github.com/soosyze/css/blob/master/LICENSE.md
 */
/* --------------------------- */
/* -------- DROPDOWN --------- */
/* --------------------------- */

var openDropdown = function () {
    const target = document.querySelector(this.getAttribute("data-target"));

    target.classList.toggle('show');
};

var closeDropdown = function (evt) {
    const dropdowns = document.querySelectorAll(".dropdown-menu.show");

    for (let i = 0; i < dropdowns.length; i++) {
        dropdowns[i].classList.remove('show');
    }
};


/* --------------------------- */
/* --------- DISSIM ---------- */
/* --------------------------- */

var toogle = function () {
    const target = document.querySelector(this.getAttribute("data-target"));

    target.classList.toggle('hidden');
};

var alert = function (evt) {
    const alert = evt.target.closest('.alert');

    alert.classList.toggle('hidden');
};


/* --------------------------- */
/* ----------- TAB ----------- */
/* --------------------------- */

var openTab = function (evt) {
    evt.preventDefault();
    const tabContent = document.getElementsByClassName("tab-pane");
    const tabLinks = document.getElementsByClassName("tab-links");

    for (let i = 0; i < tabContent.length; i++) {
        tabContent[i].classList.remove('active');
    }

    for (let i = 0; i < tabLinks.length; i++) {
        tabLinks[i].classList.remove('active');
    }

    const target = evt.target.closest('.tab-links');

    target.classList.add('active');
    document.querySelector(target.getAttribute("href")).classList.add('active');
};

var openSelect = function (evt) {
    evt.preventDefault();
    const tabContent = document.getElementsByClassName("select-pane");

    for (let i = 0; i < tabContent.length; i++) {
        tabContent[i].classList.remove('active');
    }

    const pane = document.querySelector('#' + evt.target.value);
    if (pane) {
        pane.classList.add('active');
    }
};


/* --------------------------- */
/* ---------- DRAWER --------- */
/* --------------------------- */

var openDrawer = function () {
    const drawer = document.querySelector(this.getAttribute("data-target"));

    drawer.classList.toggle('drawer-open');
    document.querySelector("body").style.overflow = 'hidden';
};

var closeDrawer = function (evt) {
    const drawer = evt.target.closest('.drawer');

    drawer.classList.toggle('drawer-open');
    document.querySelector("body").style.overflow = 'visible';
};


/* --------------------------- */
/* ---------- MODAL ---------- */
/* --------------------------- */

var openModal = function () {
    const modal = document.querySelector(this.getAttribute("data-target"));

    modal.classList.toggle('modal-open');
    document.querySelector("body").style.overflow = 'hidden';
};

var closeModal = function (evt) {
    const modal = evt.target.closest('.modal');

    modal.classList.toggle('modal-open');
    document.querySelector("body").style.overflow = 'visible';
};


/* --------------------------- */
/* ------- INPUT NUMBER ------ */
/* --------------------------- */

var decrement = function () {
    const input = document.querySelector(this.getAttribute("data-target"));
    const min = input.getAttribute('min');
    const step = input.getAttribute('step')
            ? input.getAttribute('step') - 0
            : 1;

    if (!input.value) {
        input.value = 0;
    }
    if (min && input.value - step <= min) {
        input.value = min;
        this.disabled = true;
    } else {
        input.value -= step;
        this.parentNode.querySelector('.input-number-increment').disabled = false;
    }
};

var increment = function (evt) {
    const input = document.querySelector(this.getAttribute("data-target"));
    const max = input.getAttribute('max');
    const step = input.getAttribute('step')
            ? Number(input.getAttribute('step'))
            : 1;

    if (!input.value) {
        input.value = 0;
    }
    if (max && Number(input.value) + step > max) {
        input.value = max;
        this.disabled = true;
    } else {
        input.value = Number(input.value) + step;
        this.parentNode.querySelector('.input-number-decrement').disabled = false;
    }
};

function strHighlight(needle, haystack, highlight = 'highlight') {
    var regEx = new RegExp(needle, "gi");

    return haystack.replace(regEx, function (a, b) {
        return `<span class="${highlight}">${a}</span>`;
    });
}

/* EVENT DELEDATE */
document.addEventListener('click', function (evt) {
    for (let target = evt.target; target && target !== this; target = target.parentNode) {
        if (target.matches('[data-toogle="modal"]')) {
            openModal.call(target, evt);
            break;
        } else if (target.matches('[data-dismiss="modal"]')) {
            closeModal.call(target, evt);
            break;
        } else if (target.matches('[data-toogle="drawer"]')) {
            openDrawer.call(target, evt);
            break;
        } else if (target.matches('[data-dismiss="drawer"]')) {
            closeDrawer.call(target, evt);
            break;
        } else if (target.matches('[data-toogle="dropdown"]')) {
            openDropdown.call(target, evt);
            break;
        } else if (target.matches('[data-toogle="tab"]')) {
            openTab.call(target, evt);
            break;
        } else if (target.matches('[data-dismiss="toogle"]')) {
            toogle.call(target, evt);
            break;
        } else if (target.matches('[data-dismiss="alert"]')) {
            alert.call(target, evt);
            break;
        } else if (target.matches('.input-number-decrement')) {
            decrement.call(target, evt);
            break;
        } else if (target.matches('.input-number-increment')) {
            increment.call(target, evt);
            break;
        }
    }
}, false);

document.addEventListener('change', function (evt) {
    for (let target = evt.target; target && target !== this; target = target.parentNode) {
        if (target.matches('[data-toogle="select"]')) {
            openSelect.call(target, evt);
            break;
        }
    }
}, false);

/* EVENT GLOBAL */
window.addEventListener('click', (evt) => {
    if (evt.target.classList.contains('drawer')) {
        closeDrawer(evt);
    } else if (evt.target.classList.contains('modal')) {
        closeModal(evt);
    } else if (!evt.target.matches('[data-toogle="dropdown"]') &&
            !evt.target.parentNode.matches('[data-toogle="dropdown"]')) {
        closeDropdown(evt);
    }
});