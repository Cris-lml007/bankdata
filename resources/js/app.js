import './bootstrap';
import Swal from 'sweetalert2';


import $ from 'jquery';

import moment from 'moment';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

window.Swal = Swal;
window.$ = window.JQuery =  $;
window.moment = moment;


if (!$.fn.tooltip) {
    $.fn.tooltip = function (option) {
        return this.each(function () {
            let instance = bootstrap.Tooltip.getInstance(this);

            if (option === 'dispose' || option === 'hide') {
                instance?.dispose();
                return;
            }

            if (!instance) {
                instance = new bootstrap.Tooltip(this, {
                    trigger: 'hover',
                    container: 'body'
                });
            }

            if (option === 'show') {
                instance.show();
            }
        });
    };
}


import Summernote from 'summernote/dist/summernote-bs5.js';
import 'summernote/dist/summernote-bs5.css';

window.Summernote = Summernote;
