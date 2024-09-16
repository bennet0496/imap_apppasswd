/*
 * Copyright (c) 2024. Bennet Becker <dev@bennet.cc>
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

function apppw_remove(id) {
    const apppw_remove_i = () => {
        rcmail.http_post("plugin.imap_apppasswd_remove", {'id': id});
    }
    const name = document.querySelector('[data-apppw-id="' + id +'"] > * > .apppw_title_text').textContent
    rcmail.confirm_dialog(rcmail.gettext("confirm_delete_single", "imap_apppasswd").replace("%password%", name), "delete", apppw_remove_i, {
        button_class: "delete"
    });
}

function apppw_add() {
    rcmail.http_post("plugin.imap_apppasswd_add");
}

function delete_all() {
    const d = () => rcmail.http_post("plugin.imap_apppasswd_delete_all");
    rcmail.confirm_dialog(rcmail.gettext("confirm_delete_all", "imap_apppasswd"), rcmail.gettext("delete_all", "imap_apppasswd"), d, {
        button_class: "delete"
    });
}

function apppw_edit(id) {
    const elm = document.querySelector('[data-apppw-id="' + id +'"] .apppw_title .apppw_title_text');
    const btn = document.querySelector('[data-apppw-id="' + id +'"] .apppw_title .apppw_title_edit');

    let box = document.createElement("input");
    box.value = elm.innerText;
    box.id = "apppw_title_box_" + id;
    box.className = "apppw_title_box";
    box.type = "text";
    elm.replaceWith(box);
    box.focus();

    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>';
    btn.title = window.rcmail.gettext("done", "imap_apppasswd")
    btn.onclick = function () {
        rcmail.http_post("plugin.imap_apppasswd_rename", {"id": id, "name": box.value});
    }
}

rcmail.addEventListener("plugin.apppw_remove_from_list", function (data){
    // alert();
    if (data.id === "all") {
        document.querySelectorAll('[data-apppw-id]').forEach((e) => e.remove());
    } else {
        document.querySelector('[data-apppw-id="' + data.id + '"]').remove();
    }

    if (document.querySelector(".apppw_list > .apppw_entry") === null) {
        const npw = document.querySelector(".apppw_list > .no_passwords");
        if (npw) {
            npw.classList.remove("hidden");
        }
    }
}, true);

rcmail.addEventListener("plugin.apppw_add", function (data) {
    const node = document.querySelector("#new_entry_template").content.cloneNode(true);
    node.querySelector("input.apppw_password").value = data.passwd;

    node.querySelector(".apppw_entry").dataset.apppwId = data.id;
    node.querySelector(".apppw_content_action.toggle_vis").onclick = function (event) {
        const box = document.querySelector("[data-apppw-id='"+data.id+"'] > * > input.apppw_password");
        const target = event.target.nodeName === 'svg' ? event.target.parentNode : event.target;

        if(box.type === "password") { //show
            box.type = "text";
            target.innerHTML = document.querySelector("#symbol_hide").content.firstElementChild.outerHTML;
        } else if (box.type === "text") {
            box.type = "password";
            target.innerHTML = document.querySelector("#symbol_show").content.firstElementChild.outerHTML;
        }
    };

    node.querySelector(".apppw_content_action.copy").onclick = function (_) {
        navigator.clipboard.writeText(data.passwd).then(() => {
            window.rcmail.display_message(window.rcmail.gettext("copied", "imap_apppasswd"));
        }).catch(() => {
            window.rcmail.display_message(window.rcmail.gettext("copy_failed_check_perms", "imap_apppasswd"), "error");
        });
    }

    node.querySelector(".apppw_delete").onclick = function (event) {
        event.target.innerText = rcmail.gettext("delete", "imap_apppasswd");
        document.querySelector("[data-apppw-id=\"" + data.id +"\"] .apppw_content").remove();
        event.target.onclick = () => apppw_remove(data.id);
        //rename apppw_title_box
        const value = document.querySelector("[data-apppw-id=\"" + data.id +"\"] > * > .apppw_title_box").value
        rcmail.http_post("plugin.imap_apppasswd_rename", {"id": data.id, "name": value});
    }

    node.querySelector(".apppw_title_edit").onclick = function () {
        const value = document.querySelector("[data-apppw-id=\"" + data.id +"\"] > * > .apppw_title_box").value
        rcmail.http_post("plugin.imap_apppasswd_rename", {"id": data.id, "name": value});
    }

    document.getElementById("apppw_list").append(node);
    const npw = document.querySelector(".apppw_list > .no_passwords");
    if (npw) {
        npw.classList.add("hidden");
    }
});

rcmail.addEventListener("plugin.apppw_renamed", function(data) {
    const box = document.querySelector('[data-apppw-id="' + data.id +'"] .apppw_title .apppw_title_box');
    const btn = document.querySelector('[data-apppw-id="' + data.id +'"] .apppw_title .apppw_title_edit');

    const span = document.createElement("span");
    span.className = "apppw_title_text";
    span.innerText = data.name;

    box.replaceWith(span);
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg>';
    btn.onclick = () => apppw_edit(data.id);
});

rcmail.addEventListener("init", function (ev) {
    // console.log(rcmail, window.rcmail)
    rcmail.register_command("plugin.imap_apppasswd.apppw_add", apppw_add, true)
    rcmail.register_command("plugin.imap_apppasswd.apppw_remove_all", delete_all, true)

    if (rcmail.task === "settings" &&  rcmail.env.action === "plugin.imap_apppasswd.history") {
        rcmail.set_page_buttons();
        rcmail.init_pagejumper('.pagenav > input');
        rcmail.update_pagejumper();
        rcmail.list_page = function (page) {
            if (page === 'next')
                page = this.env.current_page+1;
            else if (page === 'last')
                page = this.env.pagecount;
            else if (page === 'prev' && this.env.current_page > 1)
                page = this.env.current_page-1;
            else if (page === 'first' && this.env.current_page > 1)
                page = 1;

            if (page > 0 && page <= this.env.pagecount) {
                this.env.current_page = page;

                rcmail.goto_url("plugin.imap_apppasswd.history", {_pwid: rcmail.env.pwid, _page: page})
            }
        }
    }
})

window.addEventListener("load",function (event) {
    // console.log("load", event);
    document.querySelectorAll("span.apppw_lastused, span.apppw_created").forEach((value) => {
        // console.log(value);
        const ts = Date.parse(value.title)
        if (!Number.isNaN(ts)){
            value.title = new Date(ts).toLocaleString(undefined, {dateStyle:"full", timeStyle: "long"});
        }
    });
    document.querySelectorAll("td.timestamp").forEach((value) => {
        // console.log(value);
        const ts = Date.parse(value.innerHTML)
        if (!Number.isNaN(ts)){
            value.innerHTML = new Date(ts).toLocaleString(undefined, {dateStyle:"medium", timeStyle: "long"});
        }
    });
});