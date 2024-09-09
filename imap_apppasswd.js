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
    rcmail.http_post("plugin.imap_apppasswd_remove", {'id': id});
    // document.querySelector('[data-apppw-id="' + id +'"]').remove();
}

function apppw_add() {
    rcmail.http_post("plugin.imap_apppasswd_add");
}

function delete_all() {
    rcmail.http_post("plugin.imap_apppasswd_delete_all");
}

function apppw_edit(id) {
    let elm = document.querySelector('[data-apppw-id="' + id +'"] .apppw_title .apppw_title_text');
    let btn = document.querySelector('[data-apppw-id="' + id +'"] .apppw_title .apppw_title_edit');

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
    document.querySelector('[data-apppw-id="' + data.id +'"]').remove();
    if (document.querySelector(".apppw_list > .apppw_entry") === null) {
        const npw = document.querySelector(".apppw_list > .no_passwords");
        if (npw) {
            npw.classList.remove("hidden");
        }
    }
}, true);

rcmail.addEventListener("plugin.apppw_add", function (data) {
    let node = document.createElement("div")
    node.className = "apppw_entry";
    node.dataset.apppwId = data.id;

    let title = document.createElement("span")
    title.className = "apppw_title";
    // title.innerText = "Unnamed";

    let title_box = document.createElement("input");
    title_box.value = window.rcmail.gettext("unnamed_app", "imap_apppasswd");
    title_box.id = "apppw_title_box_" + data.id;
    title_box.className = "apppw_title_box";
    title_box.type = "text";

    let title_btn = document.createElement("a");
    title_btn.className = "apppw_title_edit";
    title_btn.title = window.rcmail.gettext("done", "imap_apppasswd");
    title_btn.onclick = function () {
        rcmail.http_post("plugin.imap_apppasswd_rename", {"id": data.id, "name": title_box.value});
    };
    title_btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z"/></svg>';

    let content = document.createElement("span")
    content.className = "apppw_content";

    let box = document.createElement("input");
    box.value = data.passwd;
    box.id = "apppw_content_box_" + data.id;
    box.type = "password";
    // box.readOnly = true;
    box.onkeydown = function (ev) {
        return false;
    }


    let copy = document.createElement("span");
    copy.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-560h80v560h440v80H200Zm160-240v-480 480Z"/></svg>'
    copy.className = "apppw_content_action";
    copy.onclick = function () {
        navigator.clipboard.writeText(data.passwd).then(() => {
            window.rcmail.display_message(window.rcmail.gettext("copied", "imap_apppasswd"));
        }).catch(() => {
            window.rcmail.display_message(window.rcmail.gettext("copy_failed_check_perms", "imap_apppasswd"), "error");
        });
    }
    let show = document.createElement("span");
    show.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/></svg>';
    show.className = "apppw_content_action";
    show.onclick = function (ev) {
        let box = document.getElementById("apppw_content_box_" + data.id);
        let target = ev.target.nodeName === 'svg' ? ev.target.parentNode : ev.target;

        if(box.type === "password") { //show
            box.type = "text";
            target.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/></svg>'
        } else if (box.type === "text") {
            box.type = "password";
            target.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/></svg>';
        }
    }

    let ok = document.createElement("a");
    ok.className = "apppw_delete";
    ok.innerText = rcmail.gettext("ok", "imap_apppasswd");
    ok.onclick = function (ev) {
        ev.target.innerText = rcmail.gettext("delete", "imap_apppasswd");
        document.querySelector("[data-apppw-id=\"" + data.id +"\"] .apppw_content").remove();
        ev.target.onclick = () => apppw_remove(data.id);
        //rename
        rcmail.http_post("plugin.imap_apppasswd_rename", {"id": data.id, "name": title_box.value});
    }

    content.append(box);
    content.append(show);
    content.append(copy);
    title.append(title_box);
    title.append(title_btn)
    node.append(title);
    node.append(content);
    node.append(ok);

    document.getElementById("apppw_list").append(node);
    const npw = document.querySelector(".apppw_list > .no_passwords");
    if (npw) {
        npw.classList.add("hidden");
    }
});

rcmail.addEventListener("plugin.apppw_renamed", function(data) {
    let box = document.querySelector('[data-apppw-id="' + data.id +'"] .apppw_title .apppw_title_box');
    let btn = document.querySelector('[data-apppw-id="' + data.id +'"] .apppw_title .apppw_title_edit');

    let span = document.createElement("span");
    span.className = "apppw_title_text";
    span.innerText = data.name;

    box.replaceWith(span);
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/></svg>';
    btn.onclick = () => apppw_edit(data.id);
});

rcmail.addEventListener("init", function (ev) {
    console.log(rcmail, window.rcmail)
    rcmail.register_command("plugin.imap_apppasswd.apppw_add", apppw_add, true)
    rcmail.register_command("plugin.imap_apppasswd.apppw_remove_all", delete_all, true)
})

window.addEventListener("load",function (event) {
    console.log("load", event);
    document.querySelectorAll("span.apppw_lastused, span.apppw_created").forEach((value) => {
        console.log(value);
        const ts = Date.parse(value.title)
        if (!Number.isNaN(ts)){
            value.title = new Date(ts).toLocaleString(undefined, {dateStyle:"full", timeStyle: "long"});
        }
    });
});