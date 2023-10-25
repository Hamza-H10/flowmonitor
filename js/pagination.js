/* * * * * * * * * * * * * * * * *
 * Pagination
 * javascript page navigation
 * * * * * * * * * * * * * * * * */

var Pagination = {

    code: '',

    // --------------------
    // Utility
    // --------------------

    // converting initialize data
    Extend: function(data) {
        data = data || {};
        Pagination.size = data.size || 300;
        Pagination.page = data.page || 1;
        Pagination.step = data.step || 3;
        Pagination.myFunc = data.myFunc;
    },

    // add pages by number (from [s] to [f])
    Add: function(s, f) {
        for (var i = s; i < f; i++) {
            Pagination.code += '<a class="item">' + i + '</a>';
        }
    },

    // add last page with separator
    Last: function() {
        Pagination.code += '<i class="disabled item">...</i><a class="item">' + Pagination.size + '</a>';
    },

    // add first page with separator
    First: function() {
        Pagination.code += '<a class="item">1</a><i class="disabled item">...</i>';
    },



    // --------------------
    // Handlers
    // --------------------

    // change page
    Click: function() {
        Pagination.page = +this.innerHTML;
        Pagination.myFunc(Pagination.page);
        Pagination.Start();
    },

    // // previous page
    // Prev: function() {
    //     Pagination.page--;
    //     if (Pagination.page < 1) {
    //         Pagination.page = 1;
    //     }
    //     Pagination.Start();
    // },

    // // next page
    // Next: function() {
    //     Pagination.page++;
    //     if (Pagination.page > Pagination.size) {
    //         Pagination.page = Pagination.size;
    //     }
    //     Pagination.Start();
    // },



    // --------------------
    // Script
    // --------------------

    // binding pages
    Bind: function() {
        var a = Pagination.e.getElementsByTagName('a');
        for (var i = 0; i < a.length; i++) {
            if (parseInt(a[i].innerHTML) == Pagination.page) 
                a[i].className = 'active item';
            else
                a[i].addEventListener('click', Pagination.Click, false);
        }
    },

    // write pagination
    Finish: function() {
        Pagination.e.innerHTML = Pagination.code;
        Pagination.code = '';
        Pagination.Bind();
    },

    // find pagination type
    Start: function() {
        if (Pagination.size < Pagination.step * 2 + 6) {
            Pagination.Add(1, parseInt(Pagination.size) + 1);
        }
        else if (Pagination.page < Pagination.step * 2 + 1) {
            Pagination.Add(1, parseInt(Pagination.step) * 2 + 4);
            Pagination.Last();
        }
        else if (Pagination.page > Pagination.size - Pagination.step * 2) {
            Pagination.First();
            Pagination.Add(parseInt(Pagination.size) - parseInt(Pagination.step) * 2 - 2, parseInt(Pagination.size) + 1);
        }
        else {
            Pagination.First();
            Pagination.Add(parseInt(Pagination.page) - parseInt(Pagination.step), parseInt(Pagination.page) + parseInt(Pagination.step) + 1);
            Pagination.Last();
        }
        Pagination.Finish();
    },



    // --------------------
    // Initialization
    // --------------------

    // binding buttons
    Buttons: function(e) {
        var nav = e.getElementsByTagName('a');
        //nav[0].addEventListener('click', Pagination.Prev, false);
        //nav[1].addEventListener('click', Pagination.Next, false);
    },

    // create skeleton
    Create: function(e) {
        e.innerHTML = '<div class="ui pagination menu"></div>';
        Pagination.e = e.getElementsByTagName('div')[0];
        Pagination.Buttons(e);
    },

    // init
    Init: function(e, data) {
        Pagination.Extend(data);
        Pagination.Create(e);
        Pagination.Start();
    }
};




