!function(){var e={150:function(){function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}!function(){"use strict";"object"!==e(window.SUI)&&(window.SUI={});var t=t||{};t.KeyCode={BACKSPACE:8,TAB:9,RETURN:13,ESC:27,SPACE:32,PAGE_UP:33,PAGE_DOWN:34,END:35,HOME:36,LEFT:37,UP:38,RIGHT:39,DOWN:40,DELETE:46},t.Utils=t.Utils||{},t.Utils.remove=function(e){return e.remove&&"function"===typeof e.remove?e.remove():!(!e.parentNode||!e.parentNode.removeChild||"function"!==typeof e.parentNode.removeChild)&&e.parentNode.removeChild(e)},t.Utils.isFocusable=function(e){if(0<e.tabIndex||0===e.tabIndex&&null!==e.getAttribute("tabIndex"))return!0;if(e.disabled)return!1;switch(e.nodeName){case"A":return!!e.href&&"ignore"!=e.rel;case"INPUT":return"hidden"!=e.type&&"file"!=e.type;case"BUTTON":case"SELECT":case"TEXTAREA":return!0;default:return!1}},t.Utils.simulateClick=function(e){var t=new MouseEvent("click",{bubbles:!0,cancelable:!0,view:window});e.dispatchEvent(t)},t.Utils.IgnoreUtilFocusChanges=!1,t.Utils.dialogOpenClass="sui-has-modal",t.Utils.focusFirstDescendant=function(e){for(var a=0;a<e.childNodes.length;a++){var i=e.childNodes[a];if(t.Utils.attemptFocus(i)||t.Utils.focusFirstDescendant(i))return!0}return!1},t.Utils.focusLastDescendant=function(e){for(var a=e.childNodes.length-1;0<=a;a--){var i=e.childNodes[a];if(t.Utils.attemptFocus(i)||t.Utils.focusLastDescendant(i))return!0}return!1},t.Utils.attemptFocus=function(e){if(!t.Utils.isFocusable(e))return!1;t.Utils.IgnoreUtilFocusChanges=!0;try{e.focus()}catch(e){}return t.Utils.IgnoreUtilFocusChanges=!1,document.activeElement===e},t.OpenDialogList=t.OpenDialogList||new Array(0),t.getCurrentDialog=function(){if(t.OpenDialogList&&t.OpenDialogList.length)return t.OpenDialogList[t.OpenDialogList.length-1]},t.closeCurrentDialog=function(){var e=t.getCurrentDialog();return!!e&&(e.close(),!0)},t.handleEscape=function(e){(e.which||e.keyCode)===t.KeyCode.ESC&&t.closeCurrentDialog()&&e.stopPropagation()},t.Dialog=function(a,i,o,s){var d=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],r=!(arguments.length>5&&void 0!==arguments[5])||arguments[5];if(this.dialogNode=document.getElementById(a),null===this.dialogNode)throw new Error('No element found with id="'+a+'".');var l=["dialog","alertdialog"];if(!(this.dialogNode.getAttribute("role")||"").trim().split(/\s+/g).some((function(e){return l.some((function(t){return e===t}))})))throw new Error("Dialog() requires a DOM element with ARIA role of dialog or alertdialog.");this.isCloseOnEsc=d;var n=new Event("open");this.dialogNode.dispatchEvent(n);var c="sui-modal";if(this.dialogNode.parentNode.classList.contains(c)?this.backdropNode=this.dialogNode.parentNode:(this.backdropNode=document.createElement("div"),this.backdropNode.className=c,this.backdropNode.setAttribute("data-markup","new"),this.dialogNode.parentNode.insertBefore(this.backdropNode,this.dialogNodev),this.backdropNode.appendChild(this.dialogNode)),this.backdropNode.classList.add("sui-active"),document.body.parentNode.classList.add(t.Utils.dialogOpenClass),"string"===typeof i)this.focusAfterClosed=document.getElementById(i);else{if("object"!==e(i))throw new Error("the focusAfterClosed parameter is required for the aria.Dialog constructor.");this.focusAfterClosed=i}"string"===typeof o?this.focusFirst=document.getElementById(o):"object"===e(o)?this.focusFirst=o:this.focusFirst=null;var u=document.createElement("div");this.preNode=this.dialogNode.parentNode.insertBefore(u,this.dialogNode),this.preNode.tabIndex=0,"boolean"===typeof s&&!0===s&&(this.preNode.classList.add("sui-modal-overlay"),this.preNode.onclick=function(){t.getCurrentDialog().close()});var m=document.createElement("div");this.postNode=this.dialogNode.parentNode.insertBefore(m,this.dialogNode.nextSibling),this.postNode.tabIndex=0,0<t.OpenDialogList.length&&t.getCurrentDialog().removeListeners(),this.addListeners(),t.OpenDialogList.push(this),r?(this.dialogNode.classList.add("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")):(this.dialogNode.classList.remove("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")),this.focusFirst?this.focusFirst.focus():t.Utils.focusFirstDescendant(this.dialogNode),this.lastFocus=document.activeElement;var C=new Event("afterOpen");this.dialogNode.dispatchEvent(C)},t.Dialog.prototype.close=function(){var e=!(arguments.length>0&&void 0!==arguments[0])||arguments[0],a=this,i=new Event("close");this.dialogNode.dispatchEvent(i),t.OpenDialogList.pop(),this.removeListeners(),this.preNode.parentNode.removeChild(this.preNode),this.postNode.parentNode.removeChild(this.postNode),e?(this.dialogNode.classList.add("sui-content-fade-out"),this.dialogNode.classList.remove("sui-content-fade-in")):(this.dialogNode.classList.remove("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")),this.focusAfterClosed.focus(),setTimeout((function(){a.backdropNode.classList.remove("sui-active")}),300),setTimeout((function(){var e=a.dialogNode.querySelectorAll(".sui-modal-slide");if(0<e.length){for(var t=0;t<e.length;t++)e[t].setAttribute("disabled",!0),e[t].classList.remove("sui-loaded"),e[t].classList.remove("sui-active"),e[t].setAttribute("tabindex","-1"),e[t].setAttribute("aria-hidden",!0);if(e[0].hasAttribute("data-modal-size")){var i=e[0].getAttribute("data-modal-size");switch(i){case"sm":case"small":i="sm";break;case"md":case"med":case"medium":i="md";break;case"lg":case"large":i="lg";break;case"xl":case"extralarge":case"extraLarge":case"extra-large":i="xl";break;default:i=void 0}void 0!==i&&(a.dialogNode.parentNode.classList.remove("sui-modal-sm"),a.dialogNode.parentNode.classList.remove("sui-modal-md"),a.dialogNode.parentNode.classList.remove("sui-modal-lg"),a.dialogNode.parentNode.classList.remove("sui-modal-xl"),a.dialogNode.parentNode.classList.add("sui-modal-"+i))}var o,s,d,r;if(e[0].classList.add("sui-active"),e[0].classList.add("sui-loaded"),e[0].removeAttribute("disabled"),e[0].removeAttribute("tabindex"),e[0].removeAttribute("aria-hidden"),e[0].hasAttribute("data-modal-labelledby"))o="",""===(s=e[0].getAttribute("data-modal-labelledby"))&&void 0===s||(o=s),a.dialogNode.setAttribute("aria-labelledby",o);if(e[0].hasAttribute("data-modal-describedby"))d="",""===(r=e[0].getAttribute("data-modal-describedby"))&&void 0===r||(d=r),a.dialogNode.setAttribute("aria-describedby",d)}}),350),0<t.OpenDialogList.length?t.getCurrentDialog().addListeners():document.body.parentNode.classList.remove(t.Utils.dialogOpenClass);var o=new Event("afterClose");this.dialogNode.dispatchEvent(o)},t.Dialog.prototype.replace=function(e,a,i,o){var s=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],d=!(arguments.length>5&&void 0!==arguments[5])||arguments[5],r=this;t.OpenDialogList.pop(),this.removeListeners(),t.Utils.remove(this.preNode),t.Utils.remove(this.postNode),d?(this.dialogNode.classList.add("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")):(this.dialogNode.classList.remove("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")),this.backdropNode.classList.remove("sui-active"),setTimeout((function(){var e=r.dialogNode.querySelectorAll(".sui-modal-slide");if(0<e.length){for(var t=0;t<e.length;t++)e[t].setAttribute("disabled",!0),e[t].classList.remove("sui-loaded"),e[t].classList.remove("sui-active"),e[t].setAttribute("tabindex","-1"),e[t].setAttribute("aria-hidden",!0);if(e[0].hasAttribute("data-modal-size")){var a=e[0].getAttribute("data-modal-size");switch(a){case"sm":case"small":a="sm";break;case"md":case"med":case"medium":a="md";break;case"lg":case"large":a="lg";break;case"xl":case"extralarge":case"extraLarge":case"extra-large":a="xl";break;default:a=void 0}void 0!==a&&(r.dialogNode.parentNode.classList.remove("sui-modal-sm"),r.dialogNode.parentNode.classList.remove("sui-modal-md"),r.dialogNode.parentNode.classList.remove("sui-modal-lg"),r.dialogNode.parentNode.classList.remove("sui-modal-xl"),r.dialogNode.parentNode.classList.add("sui-modal-"+a))}var i,o,s,d;if(e[0].classList.add("sui-active"),e[0].classList.add("sui-loaded"),e[0].removeAttribute("disabled"),e[0].removeAttribute("tabindex"),e[0].removeAttribute("aria-hidden"),e[0].hasAttribute("data-modal-labelledby"))i="",""===(o=e[0].getAttribute("data-modal-labelledby"))&&void 0===o||(i=o),r.dialogNode.setAttribute("aria-labelledby",i);if(e[0].hasAttribute("data-modal-describedby"))s="",""===(d=e[0].getAttribute("data-modal-describedby"))&&void 0===d||(s=d),r.dialogNode.setAttribute("aria-describedby",s)}}),350);var l=a||this.focusAfterClosed;new t.Dialog(e,l,i,o,s,d)},t.Dialog.prototype.slide=function(a,i,o){var s,d,r,l,n="sui-fadein",c=(t.getCurrentDialog(),this.dialogNode.querySelectorAll(".sui-modal-slide")),u=document.getElementById(a);switch(o){case"back":case"left":n="sui-fadein-left";break;case"next":case"right":n="sui-fadein-right";break;default:n="sui-fadein"}for(var m=0;m<c.length;m++)c[m].setAttribute("disabled",!0),c[m].classList.remove("sui-loaded"),c[m].classList.remove("sui-active"),c[m].setAttribute("tabindex","-1"),c[m].setAttribute("aria-hidden",!0);if(u.hasAttribute("data-modal-size")){var C=u.getAttribute("data-modal-size");switch(C){case"sm":case"small":C="sm";break;case"md":case"med":case"medium":C="md";break;case"lg":case"large":C="lg";break;case"xl":case"extralarge":case"extraLarge":case"extra-large":C="xl";break;default:C=void 0}void 0!==C&&(this.dialogNode.parentNode.classList.remove("sui-modal-sm"),this.dialogNode.parentNode.classList.remove("sui-modal-md"),this.dialogNode.parentNode.classList.remove("sui-modal-lg"),this.dialogNode.parentNode.classList.remove("sui-modal-xl"),this.dialogNode.parentNode.classList.add("sui-modal-"+C))}u.hasAttribute("data-modal-labelledby")&&(s="",""===(d=u.getAttribute("data-modal-labelledby"))&&void 0===d||(s=d),this.dialogNode.setAttribute("aria-labelledby",s));u.hasAttribute("data-modal-describedby")&&(r="",""===(l=u.getAttribute("data-modal-describedby"))&&void 0===l||(r=l),this.dialogNode.setAttribute("aria-describedby",r));u.classList.add("sui-active"),u.classList.add(n),u.removeAttribute("tabindex"),u.removeAttribute("aria-hidden"),setTimeout((function(){u.classList.add("sui-loaded"),u.classList.remove(n),u.removeAttribute("disabled")}),600),"string"===typeof i?this.newSlideFocus=document.getElementById(i):"object"===e(i)?this.newSlideFocus=i:this.newSlideFocus=null,this.newSlideFocus?this.newSlideFocus.focus():t.Utils.focusFirstDescendant(this.dialogNode)},t.Dialog.prototype.addListeners=function(){document.addEventListener("focus",this.trapFocus,!0),this.isCloseOnEsc&&this.dialogNode.addEventListener("keyup",t.handleEscape)},t.Dialog.prototype.removeListeners=function(){document.removeEventListener("focus",this.trapFocus,!0)},t.Dialog.prototype.trapFocus=function(e){var a=e.target.parentElement;if(!(t.Utils.IgnoreUtilFocusChanges||a&&a.classList.contains("wp-link-input"))){var i=t.getCurrentDialog();i.dialogNode.contains(e.target)?i.lastFocus=e.target:(t.Utils.focusFirstDescendant(i.dialogNode),i.lastFocus==document.activeElement&&t.Utils.focusLastDescendant(i.dialogNode),i.lastFocus=document.activeElement)}},SUI.openModal=function(e,a,i,o){var s=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],d=arguments.length>5?arguments[5]:void 0;new t.Dialog(e,a,i,o,s,d)},SUI.closeModal=function(e){t.getCurrentDialog().close(e)},SUI.replaceModal=function(e,a,i,o){var s=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],d=arguments.length>5?arguments[5]:void 0;t.getCurrentDialog().replace(e,a,i,o,s,d)},SUI.slideModal=function(e,a,i){t.getCurrentDialog().slide(e,a,i)}}(),function(t){"use strict";"object"!==e(window.SUI)&&(window.SUI={}),SUI.modalDialog=function(){return function(){var a,i,o,s,d,r,l,n,c,u,m,C;i=t("[data-modal-open]"),o=t("[data-modal-close]"),s=t("[data-modal-replace]"),d=t("[data-modal-slide]"),r=t(".sui-modal-overlay"),i.on("click",(function(i){a=t(this),l=a.attr("data-modal-open"),c=a.attr("data-modal-close-focus"),u=a.attr("data-modal-open-focus"),r=a.attr("data-modal-mask"),C=a.attr("data-modal-animated");var o="false"!==a.attr("data-esc-close");"undefined"!==e(c)&&!1!==c&&""!==c||(c=this),"undefined"!==e(u)&&!1!==u&&""!==u||(u=void 0),r="undefined"!==e(r)&&!1!==r&&"true"===r,C="undefined"===e(C)||!1===C||"false"!==C,"undefined"!==e(l)&&!1!==l&&""!==l&&SUI.openModal(l,c,u,r,o,C),i.preventDefault()})),s.on("click",(function(i){a=t(this),l=a.attr("data-modal-replace"),c=a.attr("data-modal-close-focus"),u=a.attr("data-modal-open-focus"),r=a.attr("data-modal-replace-mask");var o="false"!==a.attr("data-esc-close");"undefined"!==e(c)&&!1!==c&&""!==c||(c=void 0),"undefined"!==e(u)&&!1!==u&&""!==u||(u=void 0),r="undefined"!==e(r)&&!1!==r&&"true"===r,"undefined"!==e(l)&&!1!==l&&""!==l&&SUI.replaceModal(l,c,u,r,o,C),i.preventDefault()})),d.on("click",(function(i){a=t(this),n=a.attr("data-modal-slide"),u=a.attr("data-modal-slide-focus"),m=a.attr("data-modal-slide-intro"),"undefined"!==e(u)&&!1!==u&&""!==u||(u=void 0),"undefined"!==e(m)&&!1!==m&&""!==m||(m=""),"undefined"!==e(n)&&!1!==n&&""!==n&&SUI.slideModal(n,u,m),i.preventDefault()})),o.on("click",(function(e){SUI.closeModal(C),e.preventDefault()}))}(),this},SUI.modalDialog()}(jQuery)}},t={};function a(i){var o=t[i];if(void 0!==o)return o.exports;var s=t[i]={exports:{}};return e[i](s,s.exports,a),s.exports}a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,{a:t}),t},a.d=function(e,t){for(var i in t)a.o(t,i)&&!a.o(e,i)&&Object.defineProperty(e,i,{enumerable:!0,get:t[i]})},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){"use strict";var e=window.wp.element,t=window.jQuery,i=a.n(t);a(150);const o=e=>e.replace(/<[^>]*>?/gm,"").replace(/[ ]+/g," "),s=(e,t="error")=>`<div class="sui-notice sui-notice-${t}">\n\t\t\t\t\t\t\t<div class="sui-notice-content">\n\t\t\t\t\t\t\t\t<div class="sui-notice-message">\n\t\t\t\t\t\t\t\t\t<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>\n\t\t\t\t\t\t\t\t\t<p>${e}</p>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>`,d=e=>{const t=e.prop("selectionStart"),a=e.prop("selectionEnd");return e.val().substring(t,a)},r=()=>!!HandywriterAdmin.isBlockEditor&&HandywriterAdmin.isBlockEditor,l={};l.menuItemLarge=(0,e.createElement)("svg",{className:"handywriter-block-panel-icon",xmlns:"http://www.w3.org/2000/svg",width:"256",height:"256",viewBox:"0 0 256 256"},(0,e.createElement)("g",{strokeMiterlimit:"10",strokeWidth:"1"},(0,e.createElement)("path",{d:"M63.18 90.015c-8.872 0-19.892-3.054-28.472-8.235a3.003 3.003 0 01-1.449-2.568v-5.335a3 3 0 116 0v3.605c10.607 5.883 23.56 7.637 29.893 5.82 1.602-.459 2.728-1.134 3.092-1.852a3.06 3.06 0 01.245-.402c.591-.816.458-2.442-.395-4.833-.016-.042-.029-.084-.042-.126-.199-.646-.399-1.894.447-3.059-1.506-.872-3.053-1.743-4.07-2.253a3.001 3.001 0 011.179-5.678c.341-.019.786-.034 1.296-.051.717-.023 2.307-.075 3.665-.198-.611-1.47-.978-3.079-.155-4.578.595-1.084 1.687-1.786 2.996-1.926.675-.091 1.437-.422 1.978-.744a60.463 60.463 0 00-1.444-1.635c-2.848-3.148-6.748-7.461-6.85-12.419-.148-11.28-1.744-18.681-5.334-24.75-4.93-7.607-13.929-12.256-24.448-12.754v11.555h5.119a3 3 0 110 6h-8.119a3 3 0 01-3-3V3.014A3 3 0 0138.236.015C52.134-.34 64.315 5.487 70.832 15.594l.058.093c4.186 7.045 6.04 15.348 6.203 27.761.056 2.695 3.212 6.186 5.301 8.495 1.848 2.043 3.068 3.392 3.395 4.985.262 1.167-.021 2.461-.806 3.589-.962 1.381-2.654 2.462-4.36 3.123l.075.151c.245.493.478.959.65 1.4.021.057.042.114.062.173a3.552 3.552 0 01-.477 3.161c-.439.625-1.027 1.09-1.817 1.438l.32.191a3 3 0 011.452 2.746c-.11 1.909-1.381 2.927-2.471 3.483.621 2.484.802 5.434-.933 7.987-1.195 2.188-3.441 3.768-6.679 4.696-2.23.643-4.821.949-7.625.949zm15.012-25.721l-.066.01a.813.813 0 00.066-.01zm1.81-5.865z",transform:"matrix(.72 0 0 .72 128 128) matrix(3.89 0 0 3.89 -175.05 -175.05)"}),(0,e.createElement)("path",{d:"M44.074 34.702H22.859a3 3 0 01-3-3V18.964a3 3 0 116 0v9.737h18.214a3 3 0 01.001 6.001zM19.466 65.335a3 3 0 01-3-3v-7.426a3 3 0 013-3h25.341a3 3 0 110 6H22.466v4.426a3 3 0 01-3 3zM49.582 46.757H16.135a3 3 0 110-6h33.447a3 3 0 110 6zM63.256 46.757h-3.507a3 3 0 110-6h3.507a3 3 0 110 6z",transform:"matrix(.72 0 0 .72 128 128) matrix(3.89 0 0 3.89 -175.05 -175.05)"}),(0,e.createElement)("path",{d:"M22.859 20.957a6.508 6.508 0 01-6.5-6.5c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5-2.915 6.5-6.5 6.5zM51.333 26.707a6.508 6.508 0 01-6.5-6.5c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5-2.916 6.5-6.5 6.5zM11.628 50.257a6.508 6.508 0 01-6.5-6.5c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5-2.916 6.5-6.5 6.5zM19.466 73.342a6.508 6.508 0 01-6.5-6.501c0-3.584 2.916-6.5 6.5-6.5s6.5 2.915 6.5 6.5a6.506 6.506 0 01-6.5 6.501zM36.259 75.869a6.508 6.508 0 01-6.5-6.5c0-3.584 2.916-6.5 6.5-6.5s6.5 2.915 6.5 6.5c0 3.583-2.916 6.5-6.5 6.5z",transform:"matrix(.72 0 0 .72 128 128) matrix(3.89 0 0 3.89 -175.05 -175.05)"}))),l.menuItem=(0,e.createElement)("svg",{width:"23",height:"26",viewBox:"0 0 23 26",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("path",{d:"M16.1405 25.9629C13.7235 25.9629 10.7214 25.1147 8.384 23.6758C8.13909 23.5247 7.98926 23.2542 7.98926 22.9626V21.4809C7.98926 21.0207 8.35513 20.6477 8.80653 20.6477C9.25794 20.6477 9.62381 21.0207 9.62381 21.4809V22.4821C12.5134 24.116 16.0422 24.6031 17.7674 24.0985C18.2038 23.971 18.5106 23.7835 18.6098 23.5841C18.6291 23.5452 18.6514 23.508 18.6765 23.4725C18.8375 23.2459 18.8013 22.7943 18.5689 22.1302C18.5645 22.1185 18.561 22.1069 18.5575 22.0952C18.5032 21.9158 18.4488 21.5692 18.6792 21.2456C18.269 21.0035 17.8475 20.7616 17.5705 20.6199C17.2389 20.4508 17.0607 20.0753 17.1359 19.7053C17.2114 19.3354 17.5217 19.0632 17.8916 19.043C17.9845 19.0377 18.1058 19.0335 18.2447 19.0288C18.44 19.0224 18.8732 19.008 19.2431 18.9738C19.0767 18.5655 18.9767 18.1187 19.2009 17.7024C19.363 17.4013 19.6605 17.2063 20.0171 17.1674C20.201 17.1422 20.4086 17.0502 20.556 16.9608C20.4402 16.8197 20.2879 16.6478 20.1626 16.5067C19.3867 15.6324 18.3243 14.4346 18.2965 13.0576C18.2562 9.92479 17.8214 7.86931 16.8434 6.18376C15.5003 4.07107 13.0487 2.7799 10.1831 2.64159V5.85076H11.5776C12.0291 5.85076 12.3949 6.22375 12.3949 6.68395C12.3949 7.14415 12.0291 7.51714 11.5776 7.51714H9.36582C8.91441 7.51714 8.54855 7.14415 8.54855 6.68395V1.80006C8.54855 1.3482 8.90188 0.978536 9.34512 0.96715C13.1313 0.868555 16.4497 2.48689 18.2251 5.29391C18.2305 5.30252 18.2357 5.31113 18.2409 5.31974C19.3813 7.27635 19.8863 9.58235 19.9308 13.0298C19.946 13.7783 20.8058 14.7479 21.3749 15.3891C21.8783 15.9565 22.2107 16.3312 22.2998 16.7736C22.3711 17.0977 22.294 17.4571 22.0802 17.7704C21.8181 18.1539 21.3572 18.4542 20.8924 18.6378C20.8998 18.6527 20.9066 18.6666 20.9128 18.6797C20.9796 18.8166 21.0431 18.946 21.0899 19.0685C21.0956 19.0843 21.1014 19.1002 21.1068 19.1166C21.2019 19.4173 21.1545 19.737 20.9769 19.9945C20.8573 20.168 20.6971 20.2972 20.4819 20.3938C20.5118 20.4122 20.5413 20.4299 20.569 20.4469C20.8298 20.6063 20.9823 20.9001 20.9646 21.2095C20.9346 21.7397 20.5884 22.0225 20.2914 22.1769C20.4606 22.8668 20.5099 23.6861 20.0373 24.3951C19.7117 25.0028 19.0999 25.4416 18.2177 25.6993C17.6102 25.8779 16.9044 25.9629 16.1405 25.9629ZM20.2301 18.8194C20.2244 18.8205 20.2182 18.8213 20.2122 18.8222C20.2179 18.8216 20.2242 18.8205 20.2301 18.8194Z"}),(0,e.createElement)("path",{d:"M10.9356 10.6006H5.15614C4.70473 10.6006 4.33887 10.2276 4.33887 9.76741V6.22968C4.33887 5.76948 4.70473 5.39648 5.15614 5.39648C5.60755 5.39648 5.97342 5.76948 5.97342 6.22968V8.93394H10.9354C11.3868 8.93394 11.7527 9.30693 11.7527 9.76713C11.7527 10.2273 11.3868 10.6006 10.9356 10.6006Z"}),(0,e.createElement)("path",{d:"M4.23232 19.1077C3.78091 19.1077 3.41504 18.7347 3.41504 18.2745V16.2121C3.41504 15.7519 3.78091 15.3789 4.23232 15.3789H11.1358C11.5873 15.3789 11.9531 15.7519 11.9531 16.2121C11.9531 16.6723 11.587 17.0453 11.1358 17.0453H5.04959V18.2745C5.04959 18.7347 4.68372 19.1077 4.23232 19.1077Z"}),(0,e.createElement)("path",{d:"M12.4369 13.9496H3.32509C2.87368 13.9496 2.50781 13.5766 2.50781 13.1164C2.50781 12.6562 2.87368 12.2832 3.32509 12.2832H12.4369C12.8883 12.2832 13.2542 12.6562 13.2542 13.1164C13.2542 13.5766 12.8883 13.9496 12.4369 13.9496Z"}),(0,e.createElement)("path",{d:"M16.1613 13.9496H15.2059C14.7545 13.9496 14.3887 13.5766 14.3887 13.1164C14.3887 12.6562 14.7545 12.2832 15.2059 12.2832H16.1613C16.6128 12.2832 16.9786 12.6562 16.9786 13.1164C16.9786 13.5766 16.6128 13.9496 16.1613 13.9496Z"}),(0,e.createElement)("path",{d:"M5.15651 6.78432C4.18013 6.78432 3.38574 5.97446 3.38574 4.97908C3.38574 3.98369 4.18013 3.17383 5.15651 3.17383C6.13288 3.17383 6.92727 3.98369 6.92727 4.97908C6.92727 5.97446 6.13315 6.78432 5.15651 6.78432Z"}),(0,e.createElement)("path",{d:"M12.9133 8.38003C11.937 8.38003 11.1426 7.57017 11.1426 6.57478C11.1426 5.57939 11.937 4.76953 12.9133 4.76953C13.8897 4.76953 14.6841 5.57939 14.6841 6.57478C14.6841 7.57017 13.8897 8.38003 12.9133 8.38003Z"}),(0,e.createElement)("path",{d:"M2.09694 14.921C1.12056 14.921 0.326172 14.1112 0.326172 13.1158C0.326172 12.1204 1.12056 11.3105 2.09694 11.3105C3.07331 11.3105 3.8677 12.1204 3.8677 13.1158C3.8677 14.1112 3.07331 14.921 2.09694 14.921Z"}),(0,e.createElement)("path",{d:"M4.23268 21.3315C3.25631 21.3315 2.46191 20.5216 2.46191 19.526C2.46191 18.5306 3.25631 17.7207 4.23268 17.7207C5.20905 17.7207 6.00344 18.5303 6.00344 19.526C6.00372 20.5216 5.20932 21.3315 4.23268 21.3315Z"}),(0,e.createElement)("path",{d:"M8.8069 22.0343C7.83052 22.0343 7.03613 21.2245 7.03613 20.2291C7.03613 19.2337 7.83052 18.4238 8.8069 18.4238C9.78327 18.4238 10.5777 19.2334 10.5777 20.2291C10.5777 21.2242 9.78327 22.0343 8.8069 22.0343Z"})),l.TTS=(0,e.createElement)("svg",{width:"24",height:"24",viewBox:"0 0 24 24",version:"1.1",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("g",{id:"iconCarrier"},(0,e.createElement)("g",{stroke:"none",strokeWidth:"1",fill:"none",fillRule:"evenodd"},(0,e.createElement)("g",{id:"Media",transform:"translate(-960.000000, -144.000000)",fillRule:"nonzero"},(0,e.createElement)("g",{id:"voice_fill",transform:"translate(960.000000, 144.000000)"},(0,e.createElement)("path",{d:"M24,0 L24,24 L0,24 L0,0 L24,0 Z M12.5934901,23.257841 L12.5819402,23.2595131 L12.5108777,23.2950439 L12.4918791,23.2987469 L12.4918791,23.2987469 L12.4767152,23.2950439 L12.4056548,23.2595131 C12.3958229,23.2563662 12.3870493,23.2590235 12.3821421,23.2649074 L12.3780323,23.275831 L12.360941,23.7031097 L12.3658947,23.7234994 L12.3769048,23.7357139 L12.4804777,23.8096931 L12.4953491,23.8136134 L12.4953491,23.8136134 L12.5071152,23.8096931 L12.6106902,23.7357139 L12.6232938,23.7196733 L12.6232938,23.7196733 L12.6266527,23.7031097 L12.609561,23.275831 C12.6075724,23.2657013 12.6010112,23.2592993 12.5934901,23.257841 L12.5934901,23.257841 Z M12.8583906,23.1452862 L12.8445485,23.1473072 L12.6598443,23.2396597 L12.6498822,23.2499052 L12.6498822,23.2499052 L12.6471943,23.2611114 L12.6650943,23.6906389 L12.6699349,23.7034178 L12.6699349,23.7034178 L12.678386,23.7104931 L12.8793402,23.8032389 C12.8914285,23.8068999 12.9022333,23.8029875 12.9078286,23.7952264 L12.9118235,23.7811639 L12.8776777,23.1665331 C12.8752882,23.1545897 12.8674102,23.1470016 12.8583906,23.1452862 L12.8583906,23.1452862 Z M12.1430473,23.1473072 C12.1332178,23.1423925 12.1221763,23.1452606 12.1156365,23.1525954 L12.1099173,23.1665331 L12.0757714,23.7811639 C12.0751323,23.7926639 12.0828099,23.8018602 12.0926481,23.8045676 L12.108256,23.8032389 L12.3092106,23.7104931 L12.3186497,23.7024347 L12.3186497,23.7024347 L12.3225043,23.6906389 L12.340401,23.2611114 L12.337245,23.2485176 L12.337245,23.2485176 L12.3277531,23.2396597 L12.1430473,23.1473072 Z",id:"MingCute",fillRule:"nonzero"}),(0,e.createElement)("path",{d:"M12,2.5 C12.7796706,2.5 13.4204457,3.09488554 13.4931332,3.85553954 L13.5,4 L13.5,20 C13.5,20.8284 12.8284,21.5 12,21.5 C11.2203294,21.5 10.5795543,20.9050879 10.5068668,20.1444558 L10.5,20 L10.5,4 C10.5,3.17157 11.1716,2.5 12,2.5 Z M8,5.5 C8.82843,5.5 9.5,6.17157 9.5,7 L9.5,17 C9.5,17.8284 8.82843,18.5 8,18.5 C7.17157,18.5 6.5,17.8284 6.5,17 L6.5,7 C6.5,6.17157 7.17157,5.5 8,5.5 Z M16,5.5 C16.8284,5.5 17.5,6.17157 17.5,7 L17.5,17 C17.5,17.8284 16.8284,18.5 16,18.5 C15.1716,18.5 14.5,17.8284 14.5,17 L14.5,7 C14.5,6.17157 15.1716,5.5 16,5.5 Z M4,8.5 C4.82843,8.5 5.5,9.17157 5.5,10 L5.5,14 C5.5,14.8284 4.82843,15.5 4,15.5 C3.17157,15.5 2.5,14.8284 2.5,14 L2.5,10 C2.5,9.17157 3.17157,8.5 4,8.5 Z M20,8.5 C20.7796706,8.5 21.4204457,9.09488554 21.4931332,9.85553954 L21.5,10 L21.5,14 C21.5,14.8284 20.8284,15.5 20,15.5 C19.2203294,15.5 18.5795543,14.9050879 18.5068668,14.1444558 L18.5,14 L18.5,10 C18.5,9.17157 19.1716,8.5 20,8.5 Z",id:"形状",fill:"#000000"})))))),l.toolbarGrammar=(0,e.createElement)("svg",{width:"23",height:"25",viewBox:"0 0 23 25",fill:"none",xmlns:"http://www.w3.org/2000/svg"},(0,e.createElement)("path",{d:"M19.7907 3.25781H17.0963C16.9203 3.25781 16.7763 3.40181 16.7763 3.57781C16.7763 3.75381 16.9203 3.89781 17.0963 3.89781H20.1107V3.25781H19.7907ZM19.4707 21.4946H20.1107V22.1474H19.4707V21.4946ZM19.4707 8.45781H20.1107V9.11061H19.4707V8.45781ZM19.4707 7.15541H20.1107V7.80821H19.4707V7.15541ZM19.4707 4.54741H20.1107V5.20021H19.4707V4.54741ZM19.4707 5.85301H20.1107V6.50581H19.4707V5.85301ZM19.4707 9.76341H20.1107V10.4162H19.4707V9.76341ZM19.4707 11.5938V20.8418H20.1107V11.0658H19.4707V11.5938ZM19.4707 22.8002H20.1107V23.4402H19.4707V22.8002ZM9.01951 22.8002H9.6723V23.4402H9.01951V22.8002ZM10.3251 22.8002H10.9779V23.4402H10.3251V22.8002ZM11.6339 22.8002H12.2867V23.4402H11.6339V22.8002ZM18.1651 22.8002H18.8179V23.4402H18.1651V22.8002ZM12.9395 22.8002H13.5923V23.4402H12.9395V22.8002ZM15.5539 22.8002H16.2067V23.4402H15.5539V22.8002ZM16.8595 22.8002H17.5123V23.4402H16.8595V22.8002ZM14.2451 22.8002H14.8979V23.4402H14.2451V22.8002ZM7.7139 22.8002H5.7523V20.4258C5.7523 20.2498 5.6083 20.1058 5.4323 20.1058C5.2563 20.1058 5.1123 20.2498 5.1123 20.4258V23.4402H8.36671V22.8002H7.7139Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M17.0957 0.880859V20.4233H2.7373V6.01366L7.87011 0.880859H17.0957Z",fill:"white"}),(0,e.createElement)("path",{d:"M17.0964 20.7437H2.73797C2.56197 20.7437 2.41797 20.5997 2.41797 20.4237V6.01405C2.41797 5.92765 2.45317 5.84765 2.51077 5.78685L7.64357 0.657253C7.70437 0.596453 7.78437 0.564453 7.87077 0.564453H17.0996C17.2756 0.564453 17.4196 0.708453 17.4196 0.884453V20.4269C17.4164 20.6029 17.2756 20.7437 17.0964 20.7437V20.7437ZM3.05797 20.1037H16.7764V1.20125H8.00197L3.05797 6.14525V20.1037Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M5.0127 8.41406H14.8239V9.05406H5.0127V8.41406ZM5.0127 11.2749H14.8239V11.9149H5.0127V11.2749ZM5.0127 14.1357H14.8239V14.7757H5.0127V14.1357ZM5.0127 16.9965H14.8239V17.6365H5.0127V16.9965Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M13.1821 13.7952C12.1997 13.7952 11.2141 13.4208 10.4653 12.672C9.73887 11.9456 9.33887 10.9824 9.33887 9.95523C9.33887 8.92803 9.73887 7.96483 10.4653 7.23843C11.9629 5.74083 14.4013 5.74083 15.8989 7.23843C16.6253 7.96483 17.0253 8.92803 17.0253 9.95523C17.0253 10.9824 16.6253 11.9456 15.8989 12.672C15.1501 13.4208 14.1677 13.7952 13.1821 13.7952V13.7952ZM13.1821 6.75523C12.3629 6.75523 11.5437 7.06883 10.9197 7.69283C10.3149 8.29763 9.98207 9.10083 9.98207 9.95523C9.98207 10.8096 10.3149 11.616 10.9197 12.2176C12.1677 13.4656 14.1997 13.4656 15.4477 12.2176C16.0525 11.6128 16.3853 10.8096 16.3853 9.95523C16.3853 9.10083 16.0525 8.29443 15.4477 7.69283C14.8237 7.06563 14.0045 6.75523 13.1821 6.75523Z",fill:"#ED1C24"}),(0,e.createElement)("path",{d:"M13.1824 15.2518C11.8256 15.2518 10.4688 14.7366 9.43517 13.703C7.36797 11.6358 7.36797 8.2726 9.43517 6.2086C11.5024 4.1414 14.8656 4.1414 16.9296 6.2086C18.9968 8.2758 18.9968 11.639 16.9296 13.703C15.8992 14.7366 14.5424 15.2518 13.1824 15.2518V15.2518ZM13.1824 5.2966C11.9888 5.2966 10.7952 5.751 9.88637 6.6598C8.06877 8.4774 8.06877 11.4342 9.88637 13.2518C11.704 15.0694 14.6608 15.0694 16.4784 13.2518C18.296 11.4342 18.296 8.4774 16.4784 6.6598C15.5696 5.751 14.376 5.2966 13.1824 5.2966Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M9.66232 6.43186C7.71673 8.37746 7.71673 11.5295 9.66232 13.4751C11.6079 15.4207 14.7599 15.4207 16.7055 13.4751C18.6511 11.5295 18.6511 8.37746 16.7055 6.43186C14.7599 4.48626 11.6079 4.48626 9.66232 6.43186V6.43186ZM15.6751 12.4447C14.2991 13.8207 12.0687 13.8207 10.6959 12.4447C9.31992 11.0687 9.31992 8.83826 10.6959 7.46546C12.0719 6.08946 14.3023 6.08946 15.6751 7.46546C17.0479 8.83826 17.0479 11.0687 15.6751 12.4447Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M9.66232 6.43186C7.71673 8.37746 7.71673 11.5295 9.66232 13.4751C11.6079 15.4207 14.7599 15.4207 16.7055 13.4751C18.6511 11.5295 18.6511 8.37746 16.7055 6.43186C14.7599 4.48626 11.6079 4.48626 9.66232 6.43186V6.43186ZM16.3375 13.1071C14.5967 14.8479 11.7743 14.8479 10.0335 13.1071C8.29272 11.3663 8.29272 8.54386 10.0335 6.80306C11.7743 5.06226 14.5967 5.06226 16.3375 6.80306C18.0783 8.54386 18.0783 11.3663 16.3375 13.1071Z",fill:"white"}),(0,e.createElement)("path",{d:"M15.6741 12.4482C17.0499 11.0723 17.0499 8.84149 15.674 7.46562C14.2981 6.08975 12.0673 6.08979 10.6914 7.46571C9.31556 8.84163 9.3156 11.0724 10.6915 12.4483C12.0674 13.8241 14.2982 13.8241 15.6741 12.4482Z",fill:"white"}),(0,e.createElement)("path",{d:"M13.1821 13.7952C12.1997 13.7952 11.2141 13.4208 10.4653 12.672C9.73887 11.9456 9.33887 10.9824 9.33887 9.95523C9.33887 8.92803 9.73887 7.96483 10.4653 7.23843C11.9629 5.74083 14.4013 5.74083 15.8989 7.23843C16.6253 7.96483 17.0253 8.92803 17.0253 9.95523C17.0253 10.9824 16.6253 11.9456 15.8989 12.672C15.1501 13.4208 14.1677 13.7952 13.1821 13.7952V13.7952ZM13.1821 6.75523C12.3629 6.75523 11.5437 7.06883 10.9197 7.69283C10.3149 8.29763 9.98207 9.10083 9.98207 9.95523C9.98207 10.8096 10.3149 11.616 10.9197 12.2176C12.1677 13.4656 14.1997 13.4656 15.4477 12.2176C16.0525 11.6128 16.3853 10.8096 16.3853 9.95523C16.3853 9.10083 16.0525 8.29443 15.4477 7.69283C14.8237 7.06563 14.0045 6.75523 13.1821 6.75523Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M13.1824 15.2518C11.8256 15.2518 10.4688 14.7366 9.43517 13.703C7.36797 11.6358 7.36797 8.2726 9.43517 6.2086C11.5024 4.1414 14.8656 4.1414 16.9296 6.2086C18.9968 8.2758 18.9968 11.639 16.9296 13.703C15.8992 14.7366 14.5424 15.2518 13.1824 15.2518V15.2518ZM13.1824 5.2966C11.9888 5.2966 10.7952 5.751 9.88637 6.6598C8.06877 8.4774 8.06877 11.4342 9.88637 13.2518C11.704 15.0694 14.6608 15.0694 16.4784 13.2518C18.296 11.4342 18.296 8.4774 16.4784 6.6598C15.5696 5.751 14.376 5.2966 13.1824 5.2966Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M16.7058 13.4764C16.4882 13.694 16.2578 13.886 16.0146 14.0556L19.909 17.95C20.2578 18.2988 20.8242 18.302 21.1698 17.9532L21.1762 17.9468L21.1826 17.9404C21.5314 17.5916 21.525 17.0252 21.1794 16.6796L17.285 12.7852C17.1154 13.0284 16.9234 13.262 16.7058 13.4764Z",fill:"white"}),(0,e.createElement)("path",{d:"M16.7055 13.4758C16.6063 13.575 16.4879 13.655 16.3535 13.7126L20.2479 17.607C20.5967 17.9558 21.0095 18.1094 21.1727 17.9494L21.1759 17.9462L21.1791 17.943C21.3391 17.783 21.1823 17.367 20.8367 17.0182L16.9423 13.127C16.8847 13.2582 16.8047 13.3766 16.7055 13.4758V13.4758Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M20.5418 18.5316C20.2314 18.5316 19.9178 18.4132 19.681 18.1764L15.5146 14.01L15.8314 13.7924C16.0618 13.6324 16.2794 13.45 16.481 13.2516C16.6794 13.0532 16.8618 12.8356 17.0218 12.602L17.2394 12.2852L21.4058 16.4516C21.8794 16.9252 21.8827 17.6932 21.4123 18.1668L21.3994 18.1796C21.1594 18.4164 20.8522 18.5316 20.5418 18.5316V18.5316ZM16.5003 14.09L20.1354 17.7252C20.3594 17.9492 20.7242 17.9524 20.945 17.7284L20.9578 17.7156C21.1786 17.4948 21.1754 17.1332 20.9514 16.906L17.3163 13.2708C17.1947 13.4212 17.0666 13.5652 16.929 13.7028C16.7946 13.8404 16.6507 13.9684 16.5003 14.09V14.09Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M13.1825 10.9255C13.718 10.9255 14.1521 10.4914 14.1521 9.95593C14.1521 9.42043 13.718 8.98633 13.1825 8.98633C12.647 8.98633 12.2129 9.42043 12.2129 9.95593C12.2129 10.4914 12.647 10.9255 13.1825 10.9255Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M13.1825 11.4542C11.7649 11.4542 10.6545 10.2222 10.6097 10.1678L10.4209 9.95658L10.6097 9.74538C10.6577 9.69418 11.7649 8.45898 13.1825 8.45898C14.6001 8.45898 15.7105 9.69098 15.7553 9.74538L15.9441 9.95658L15.7553 10.1678C15.7105 10.2222 14.6001 11.4542 13.1825 11.4542ZM11.3073 9.95658C11.6529 10.2702 12.3761 10.8142 13.1857 10.8142C13.9985 10.8142 14.7185 10.2702 15.0641 9.95658C14.7185 9.64298 13.9953 9.09898 13.1857 9.09898C12.3761 9.09898 11.6529 9.64298 11.3073 9.95658V9.95658Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M13.1822 11.2452C12.4718 11.2452 11.8926 10.666 11.8926 9.95562C11.8926 9.24522 12.4718 8.66602 13.1822 8.66602C13.8926 8.66602 14.4718 9.24522 14.4718 9.95562C14.4718 10.666 13.8958 11.2452 13.1822 11.2452ZM13.1822 9.30602C12.8238 9.30602 12.5326 9.59722 12.5326 9.95562C12.5326 10.314 12.8238 10.6052 13.1822 10.6052C13.5406 10.6052 13.8318 10.314 13.8318 9.95562C13.8318 9.59722 13.5406 9.30602 13.1822 9.30602Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M7.86985 6.33341H2.73705C2.60905 6.33341 2.49065 6.25661 2.44265 6.13501C2.39465 6.01661 2.42025 5.879 2.51305 5.7862L7.64585 0.656605C7.73865 0.563805 7.87625 0.538205 7.99465 0.586205C8.11305 0.634205 8.19305 0.752605 8.19305 0.880605V6.0102C8.18985 6.1894 8.04585 6.33341 7.86985 6.33341V6.33341ZM3.50825 5.69341H7.54665V1.655L3.50825 5.69341Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M0.558594 15.6717L2.73779 14.4141L4.91699 15.6717V18.1869L2.73779 19.4477L0.558594 18.1869V15.6717Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M2.73748 19.8158L0.238281 18.3726V15.4862L2.73748 14.043L5.23668 15.4862V18.3726L2.73748 19.8158V19.8158ZM0.878281 18.0046L2.73748 19.0766L4.59668 18.0046V15.8574L2.73748 14.7854L0.878281 15.8574V18.0046Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M0.558594 9.39627L2.73779 8.13867L4.91699 9.39627V11.9115L2.73779 13.1691L0.558594 11.9115V9.39627Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M2.73748 13.5404L0.238281 12.0972V9.21078L2.73748 7.76758L5.23668 9.21078V12.0972L2.73748 13.5404V13.5404ZM0.878281 11.726L2.73748 12.798L4.59668 11.726V9.57878L2.73748 8.50678L0.878281 9.57878V11.726V11.726Z",fill:"#122436"}),(0,e.createElement)("path",{d:"M1.62402 11.3407L3.40025 9.56445L3.85279 10.017L2.07657 11.7932L1.62402 11.3407Z",fill:"white"}),(0,e.createElement)("path",{d:"M1.62402 10.019L2.07657 9.56641L3.85279 11.3426L3.40025 11.7952L1.62402 10.019Z",fill:"white"}),(0,e.createElement)("path",{d:"M2.3987 18.0618L1.4707 17.1338L1.8899 16.7114L2.3987 17.2234L3.5635 16.0586L3.9827 16.4778L2.3987 18.0618Z",fill:"white"}));var n=l;const{__:c}=wp.i18n,{createHigherOrderComponent:u}=wp.compose,{Fragment:m}=wp.element,{BlockControls:C}=wp.blockEditor,{ToolbarGroup:h,ToolbarButton:p,Icon:f}=wp.components,g=["core/paragraph","core/heading"],v=u((t=>a=>{if(!g.includes(a.name))return(0,e.createElement)(t,a);return(0,e.createElement)(m,null,(0,e.createElement)(C,{group:"block"},(0,e.createElement)(h,null,(0,e.createElement)(p,{icon:n.TTS,label:c("Turn text into audio","handywriter"),showTooltip:"true",onClick:()=>{const e=(()=>{let e=window.getSelection().toString();return e||(e=wp.data.select("core/block-editor").getBlocks().filter((e=>g.includes(e.name))).map((e=>e.attributes.content)).join("\n\n")),e})().replace(/<br\s*[\/]?>/gi,"\n");i()("#handywriter-tts-content").val(i()("<div>").html(e).text().replace(/(<([^>]+)>)/gi,"")),window.SUI.openModal("handywriter-tts-modal","wpbody-content",void 0,!0)}}))),(0,e.createElement)(t,a))}),"withToolbarButton");var L;wp.hooks.addFilter("editor.BlockEdit","handywriter-tts/toolbar-button",v,80),(L=i())(".handywriter-tts-classic-editor-btn").on("click",(function(){const e=L(this).data("editor-id"),t=i()("#wp-content-wrap").hasClass("tmce-active")?((e,t)=>{if("undefined"==typeof e&&(e=wpActiveEditor),"undefined"==typeof t&&(t=e),i()("#wp-"+e+"-wrap").hasClass("tmce-active")&&tinyMCE.get(e)){const t=tinyMCE.get(e).selection.getContent({format:"text"});return t?t.trim():tinyMCE.get(e).getContent({format:"text"})}{const e=d(i()("#"+t));return o(e||i()("#"+t).val())}})():o(d(L("#content"))||L("#content").val());L("#handywriter-tts-editor-id").val(e),L("#handywriter-tts-content").text(t),SUI.openModal("handywriter-tts-modal",this,void 0,!0,!0,!1)})),L(document).on("click","#handywriter-tts-modal-close",(function(e){e.preventDefault(),i()(".wp-toolbar, .sui-modal").removeClass("sui-has-modal sui-active"),window.SUI.closeModal()})),L(document).on("handywriter-tts-audio-generated",(function(e){i()(".wp-toolbar").removeClass("sui-has-modal"),i()(".sui-modal").removeClass("sui-active"),window.SUI.closeModal()})),L(document).on("submit","#handywriter-tts-voice-generator-form",(function(e){e.preventDefault();const t=L("#handywriter_tts_result_msg"),a=L("#handywriter-tts-generate-voice");L.ajax({url:ajaxurl,type:"POST",beforeSend:function(){t.empty(),a.addClass("sui-button-onload-text")},data:{action:"handywriter_create_audio",nonce:L("#handywriter_tts_nonce").val(),data:L(this).serialize(),title:r()?wp.data.select("core/editor").getEditedPostAttribute("title"):L("#titlewrap").find("input").val()}}).done((function(e){!function(e,t,a){if(e.success)L(document).trigger("handywriter-tts-audio-generated"),function(e){if(r()){let t="core/audio",a=wp.blocks.createBlock(t,{id:e.data.attachment_id,src:e.data.attachment_url,caption:L("#tts_disclosure").val()});wp.data.dispatch("core/block-editor").insertBlocks(a)}else if(wp&&wp.media&&wp.media.editor){wpActiveEditor&&tinyMCE&&tinyMCE.get(wpActiveEditor).selection.collapse(),wp.media.editor.activeEditor=editorID;let t=wp.media.editor.get(editorID);(!t||t.options&&t.state!==t.options.state)&&(t=wp.media.editor.add(editorID,{})),wp.media.frame=t,wp.media.frame.content.mode("browse"),wp.media.frame.on("open",(function(){null!==wp.media.frame.content.get()&&(wp.media.frame.content.get().collection._requery(!0),wp.media.frame.content.get().options.selection.reset());let t=wp.media.frame.state().get("selection"),a=wp.media.attachment(e.data.attachment_id);a.set("type","audio"),a.set("filename","handywriter-tts.mp3"),a.set("meta",{bitrate:48e3,bitrate_mode:"cbr"}),t.multiple=!1,t.add(a)}),this),wp.media.frame.open()}}(e);else{const a=s(e.data.message,"error");t.html(a)}}(e,t)})).fail((function(){const e=s(c("An error occurred while processing the request.","handywriter"),"error");t.html(e)})).always((function(){a.removeClass("sui-button-onload-text")}))}))}()}();