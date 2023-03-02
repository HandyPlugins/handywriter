!function(){var e={559:function(){!function(e){e('input[name="content_template"]').on("click",(function(){e("#content-template-selection").hide(),e("#content-generator").show();const t=e(this).data("form-heading");t&&e("#content_template_generator_title").text(t);const i=`#${e(this).val()}-wrapper`;e("#handywriter-content-form fieldset").hide(),e(i).find("input, textarea").prop("required",!0),e(i).show()})),e("#back-to-content-selection").on("click",(function(){e("#handywriter-content-form").find("input, textarea").prop("required",!1),e("#content-template-selection").show(),e("#content-generator").hide(),e("#handywriter-content-form fieldset").hide(),e("#results").html(""),e("#results").addClass("sui-hidden"),e("#submit-content-generate").removeClass("sui-button-onload-text")}));new ClipboardJS(".copy-to-clipboard").on("success",(function(t){e(t.trigger).addClass("sui-tooltip"),e(t.trigger).attr("aria-label","Copied!"),e(t.trigger).attr("data-tooltip","Copied!"),setTimeout((function(){e(t.trigger).removeClass("sui-tooltip"),e(t.trigger).removeAttr("aria-label"),e(t.trigger).removeAttr("data-tooltip")}),2e3),t.clearSelection()})),e("#handywriter-content-form").on("submit",(function(t){t.preventDefault();const i=e("#handywriter_content_template_nonce").val(),a=e("#handywriter_ajax_url").val();e.post(a,{beforeSend(){e("#submit-content-generate").addClass("sui-button-onload-text")},action:"handywriter_content_template_create_content",nonce:i,formData:e(this).serialize()},(function(t){if(t.success)e("#results").html(t.data);else{const i=`<div role="alert" id="inline-notice-general" class="sui-notice sui-notice-green sui-notice-yellow sui-notice-red sui-active" aria-live="assertive" style="display: block;">\n\t\t\t\t\t\t<div class="sui-notice-content">\n\t\t\t\t\t\t\t<div class="sui-notice-message">\n\t\t\t\t\t\t\t\t<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>\n\t\t\t\t\t\t\t\t<p>${t.data.message}</p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>`;e("#results").html(i)}e("#results").removeClass("sui-hidden")})).done((function(t){e("#submit-content-generate").removeClass("sui-button-onload-text")}))}))}(jQuery)},150:function(){function e(t){return e="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},e(t)}!function(){"use strict";"object"!==e(window.SUI)&&(window.SUI={});var t=t||{};t.KeyCode={BACKSPACE:8,TAB:9,RETURN:13,ESC:27,SPACE:32,PAGE_UP:33,PAGE_DOWN:34,END:35,HOME:36,LEFT:37,UP:38,RIGHT:39,DOWN:40,DELETE:46},t.Utils=t.Utils||{},t.Utils.remove=function(e){return e.remove&&"function"===typeof e.remove?e.remove():!(!e.parentNode||!e.parentNode.removeChild||"function"!==typeof e.parentNode.removeChild)&&e.parentNode.removeChild(e)},t.Utils.isFocusable=function(e){if(0<e.tabIndex||0===e.tabIndex&&null!==e.getAttribute("tabIndex"))return!0;if(e.disabled)return!1;switch(e.nodeName){case"A":return!!e.href&&"ignore"!=e.rel;case"INPUT":return"hidden"!=e.type&&"file"!=e.type;case"BUTTON":case"SELECT":case"TEXTAREA":return!0;default:return!1}},t.Utils.simulateClick=function(e){var t=new MouseEvent("click",{bubbles:!0,cancelable:!0,view:window});e.dispatchEvent(t)},t.Utils.IgnoreUtilFocusChanges=!1,t.Utils.dialogOpenClass="sui-has-modal",t.Utils.focusFirstDescendant=function(e){for(var i=0;i<e.childNodes.length;i++){var a=e.childNodes[i];if(t.Utils.attemptFocus(a)||t.Utils.focusFirstDescendant(a))return!0}return!1},t.Utils.focusLastDescendant=function(e){for(var i=e.childNodes.length-1;0<=i;i--){var a=e.childNodes[i];if(t.Utils.attemptFocus(a)||t.Utils.focusLastDescendant(a))return!0}return!1},t.Utils.attemptFocus=function(e){if(!t.Utils.isFocusable(e))return!1;t.Utils.IgnoreUtilFocusChanges=!0;try{e.focus()}catch(e){}return t.Utils.IgnoreUtilFocusChanges=!1,document.activeElement===e},t.OpenDialogList=t.OpenDialogList||new Array(0),t.getCurrentDialog=function(){if(t.OpenDialogList&&t.OpenDialogList.length)return t.OpenDialogList[t.OpenDialogList.length-1]},t.closeCurrentDialog=function(){var e=t.getCurrentDialog();return!!e&&(e.close(),!0)},t.handleEscape=function(e){(e.which||e.keyCode)===t.KeyCode.ESC&&t.closeCurrentDialog()&&e.stopPropagation()},t.Dialog=function(i,a,o,s){var d=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],n=!(arguments.length>5&&void 0!==arguments[5])||arguments[5];if(this.dialogNode=document.getElementById(i),null===this.dialogNode)throw new Error('No element found with id="'+i+'".');var r=["dialog","alertdialog"];if(!(this.dialogNode.getAttribute("role")||"").trim().split(/\s+/g).some((function(e){return r.some((function(t){return e===t}))})))throw new Error("Dialog() requires a DOM element with ARIA role of dialog or alertdialog.");this.isCloseOnEsc=d;var l=new Event("open");this.dialogNode.dispatchEvent(l);var c="sui-modal";if(this.dialogNode.parentNode.classList.contains(c)?this.backdropNode=this.dialogNode.parentNode:(this.backdropNode=document.createElement("div"),this.backdropNode.className=c,this.backdropNode.setAttribute("data-markup","new"),this.dialogNode.parentNode.insertBefore(this.backdropNode,this.dialogNodev),this.backdropNode.appendChild(this.dialogNode)),this.backdropNode.classList.add("sui-active"),document.body.parentNode.classList.add(t.Utils.dialogOpenClass),"string"===typeof a)this.focusAfterClosed=document.getElementById(a);else{if("object"!==e(a))throw new Error("the focusAfterClosed parameter is required for the aria.Dialog constructor.");this.focusAfterClosed=a}"string"===typeof o?this.focusFirst=document.getElementById(o):"object"===e(o)?this.focusFirst=o:this.focusFirst=null;var u=document.createElement("div");this.preNode=this.dialogNode.parentNode.insertBefore(u,this.dialogNode),this.preNode.tabIndex=0,"boolean"===typeof s&&!0===s&&(this.preNode.classList.add("sui-modal-overlay"),this.preNode.onclick=function(){t.getCurrentDialog().close()});var m=document.createElement("div");this.postNode=this.dialogNode.parentNode.insertBefore(m,this.dialogNode.nextSibling),this.postNode.tabIndex=0,0<t.OpenDialogList.length&&t.getCurrentDialog().removeListeners(),this.addListeners(),t.OpenDialogList.push(this),n?(this.dialogNode.classList.add("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")):(this.dialogNode.classList.remove("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")),this.focusFirst?this.focusFirst.focus():t.Utils.focusFirstDescendant(this.dialogNode),this.lastFocus=document.activeElement;var g=new Event("afterOpen");this.dialogNode.dispatchEvent(g)},t.Dialog.prototype.close=function(){var e=!(arguments.length>0&&void 0!==arguments[0])||arguments[0],i=this,a=new Event("close");this.dialogNode.dispatchEvent(a),t.OpenDialogList.pop(),this.removeListeners(),this.preNode.parentNode.removeChild(this.preNode),this.postNode.parentNode.removeChild(this.postNode),e?(this.dialogNode.classList.add("sui-content-fade-out"),this.dialogNode.classList.remove("sui-content-fade-in")):(this.dialogNode.classList.remove("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")),this.focusAfterClosed.focus(),setTimeout((function(){i.backdropNode.classList.remove("sui-active")}),300),setTimeout((function(){var e=i.dialogNode.querySelectorAll(".sui-modal-slide");if(0<e.length){for(var t=0;t<e.length;t++)e[t].setAttribute("disabled",!0),e[t].classList.remove("sui-loaded"),e[t].classList.remove("sui-active"),e[t].setAttribute("tabindex","-1"),e[t].setAttribute("aria-hidden",!0);if(e[0].hasAttribute("data-modal-size")){var a=e[0].getAttribute("data-modal-size");switch(a){case"sm":case"small":a="sm";break;case"md":case"med":case"medium":a="md";break;case"lg":case"large":a="lg";break;case"xl":case"extralarge":case"extraLarge":case"extra-large":a="xl";break;default:a=void 0}void 0!==a&&(i.dialogNode.parentNode.classList.remove("sui-modal-sm"),i.dialogNode.parentNode.classList.remove("sui-modal-md"),i.dialogNode.parentNode.classList.remove("sui-modal-lg"),i.dialogNode.parentNode.classList.remove("sui-modal-xl"),i.dialogNode.parentNode.classList.add("sui-modal-"+a))}var o,s,d,n;if(e[0].classList.add("sui-active"),e[0].classList.add("sui-loaded"),e[0].removeAttribute("disabled"),e[0].removeAttribute("tabindex"),e[0].removeAttribute("aria-hidden"),e[0].hasAttribute("data-modal-labelledby"))o="",""===(s=e[0].getAttribute("data-modal-labelledby"))&&void 0===s||(o=s),i.dialogNode.setAttribute("aria-labelledby",o);if(e[0].hasAttribute("data-modal-describedby"))d="",""===(n=e[0].getAttribute("data-modal-describedby"))&&void 0===n||(d=n),i.dialogNode.setAttribute("aria-describedby",d)}}),350),0<t.OpenDialogList.length?t.getCurrentDialog().addListeners():document.body.parentNode.classList.remove(t.Utils.dialogOpenClass);var o=new Event("afterClose");this.dialogNode.dispatchEvent(o)},t.Dialog.prototype.replace=function(e,i,a,o){var s=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],d=!(arguments.length>5&&void 0!==arguments[5])||arguments[5],n=this;t.OpenDialogList.pop(),this.removeListeners(),t.Utils.remove(this.preNode),t.Utils.remove(this.postNode),d?(this.dialogNode.classList.add("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")):(this.dialogNode.classList.remove("sui-content-fade-in"),this.dialogNode.classList.remove("sui-content-fade-out")),this.backdropNode.classList.remove("sui-active"),setTimeout((function(){var e=n.dialogNode.querySelectorAll(".sui-modal-slide");if(0<e.length){for(var t=0;t<e.length;t++)e[t].setAttribute("disabled",!0),e[t].classList.remove("sui-loaded"),e[t].classList.remove("sui-active"),e[t].setAttribute("tabindex","-1"),e[t].setAttribute("aria-hidden",!0);if(e[0].hasAttribute("data-modal-size")){var i=e[0].getAttribute("data-modal-size");switch(i){case"sm":case"small":i="sm";break;case"md":case"med":case"medium":i="md";break;case"lg":case"large":i="lg";break;case"xl":case"extralarge":case"extraLarge":case"extra-large":i="xl";break;default:i=void 0}void 0!==i&&(n.dialogNode.parentNode.classList.remove("sui-modal-sm"),n.dialogNode.parentNode.classList.remove("sui-modal-md"),n.dialogNode.parentNode.classList.remove("sui-modal-lg"),n.dialogNode.parentNode.classList.remove("sui-modal-xl"),n.dialogNode.parentNode.classList.add("sui-modal-"+i))}var a,o,s,d;if(e[0].classList.add("sui-active"),e[0].classList.add("sui-loaded"),e[0].removeAttribute("disabled"),e[0].removeAttribute("tabindex"),e[0].removeAttribute("aria-hidden"),e[0].hasAttribute("data-modal-labelledby"))a="",""===(o=e[0].getAttribute("data-modal-labelledby"))&&void 0===o||(a=o),n.dialogNode.setAttribute("aria-labelledby",a);if(e[0].hasAttribute("data-modal-describedby"))s="",""===(d=e[0].getAttribute("data-modal-describedby"))&&void 0===d||(s=d),n.dialogNode.setAttribute("aria-describedby",s)}}),350);var r=i||this.focusAfterClosed;new t.Dialog(e,r,a,o,s,d)},t.Dialog.prototype.slide=function(i,a,o){var s,d,n,r,l="sui-fadein",c=(t.getCurrentDialog(),this.dialogNode.querySelectorAll(".sui-modal-slide")),u=document.getElementById(i);switch(o){case"back":case"left":l="sui-fadein-left";break;case"next":case"right":l="sui-fadein-right";break;default:l="sui-fadein"}for(var m=0;m<c.length;m++)c[m].setAttribute("disabled",!0),c[m].classList.remove("sui-loaded"),c[m].classList.remove("sui-active"),c[m].setAttribute("tabindex","-1"),c[m].setAttribute("aria-hidden",!0);if(u.hasAttribute("data-modal-size")){var g=u.getAttribute("data-modal-size");switch(g){case"sm":case"small":g="sm";break;case"md":case"med":case"medium":g="md";break;case"lg":case"large":g="lg";break;case"xl":case"extralarge":case"extraLarge":case"extra-large":g="xl";break;default:g=void 0}void 0!==g&&(this.dialogNode.parentNode.classList.remove("sui-modal-sm"),this.dialogNode.parentNode.classList.remove("sui-modal-md"),this.dialogNode.parentNode.classList.remove("sui-modal-lg"),this.dialogNode.parentNode.classList.remove("sui-modal-xl"),this.dialogNode.parentNode.classList.add("sui-modal-"+g))}u.hasAttribute("data-modal-labelledby")&&(s="",""===(d=u.getAttribute("data-modal-labelledby"))&&void 0===d||(s=d),this.dialogNode.setAttribute("aria-labelledby",s));u.hasAttribute("data-modal-describedby")&&(n="",""===(r=u.getAttribute("data-modal-describedby"))&&void 0===r||(n=r),this.dialogNode.setAttribute("aria-describedby",n));u.classList.add("sui-active"),u.classList.add(l),u.removeAttribute("tabindex"),u.removeAttribute("aria-hidden"),setTimeout((function(){u.classList.add("sui-loaded"),u.classList.remove(l),u.removeAttribute("disabled")}),600),"string"===typeof a?this.newSlideFocus=document.getElementById(a):"object"===e(a)?this.newSlideFocus=a:this.newSlideFocus=null,this.newSlideFocus?this.newSlideFocus.focus():t.Utils.focusFirstDescendant(this.dialogNode)},t.Dialog.prototype.addListeners=function(){document.addEventListener("focus",this.trapFocus,!0),this.isCloseOnEsc&&this.dialogNode.addEventListener("keyup",t.handleEscape)},t.Dialog.prototype.removeListeners=function(){document.removeEventListener("focus",this.trapFocus,!0)},t.Dialog.prototype.trapFocus=function(e){if(!t.Utils.IgnoreUtilFocusChanges){var i=t.getCurrentDialog();i.dialogNode.contains(e.target)?i.lastFocus=e.target:(t.Utils.focusFirstDescendant(i.dialogNode),i.lastFocus==document.activeElement&&t.Utils.focusLastDescendant(i.dialogNode),i.lastFocus=document.activeElement)}},SUI.openModal=function(e,i,a,o){var s=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],d=arguments.length>5?arguments[5]:void 0;new t.Dialog(e,i,a,o,s,d)},SUI.closeModal=function(e){t.getCurrentDialog().close(e)},SUI.replaceModal=function(e,i,a,o){var s=!(arguments.length>4&&void 0!==arguments[4])||arguments[4],d=arguments.length>5?arguments[5]:void 0;t.getCurrentDialog().replace(e,i,a,o,s,d)},SUI.slideModal=function(e,i,a){t.getCurrentDialog().slide(e,i,a)}}(),function(t){"use strict";"object"!==e(window.SUI)&&(window.SUI={}),SUI.modalDialog=function(){return function(){var i,a,o,s,d,n,r,l,c,u,m,g;a=t("[data-modal-open]"),o=t("[data-modal-close]"),s=t("[data-modal-replace]"),d=t("[data-modal-slide]"),n=t(".sui-modal-overlay"),a.on("click",(function(a){i=t(this),r=i.attr("data-modal-open"),c=i.attr("data-modal-close-focus"),u=i.attr("data-modal-open-focus"),n=i.attr("data-modal-mask"),g=i.attr("data-modal-animated");var o="false"!==i.attr("data-esc-close");"undefined"!==e(c)&&!1!==c&&""!==c||(c=this),"undefined"!==e(u)&&!1!==u&&""!==u||(u=void 0),n="undefined"!==e(n)&&!1!==n&&"true"===n,g="undefined"===e(g)||!1===g||"false"!==g,"undefined"!==e(r)&&!1!==r&&""!==r&&SUI.openModal(r,c,u,n,o,g),a.preventDefault()})),s.on("click",(function(a){i=t(this),r=i.attr("data-modal-replace"),c=i.attr("data-modal-close-focus"),u=i.attr("data-modal-open-focus"),n=i.attr("data-modal-replace-mask");var o="false"!==i.attr("data-esc-close");"undefined"!==e(c)&&!1!==c&&""!==c||(c=void 0),"undefined"!==e(u)&&!1!==u&&""!==u||(u=void 0),n="undefined"!==e(n)&&!1!==n&&"true"===n,"undefined"!==e(r)&&!1!==r&&""!==r&&SUI.replaceModal(r,c,u,n,o,g),a.preventDefault()})),d.on("click",(function(a){i=t(this),l=i.attr("data-modal-slide"),u=i.attr("data-modal-slide-focus"),m=i.attr("data-modal-slide-intro"),"undefined"!==e(u)&&!1!==u&&""!==u||(u=void 0),"undefined"!==e(m)&&!1!==m&&""!==m||(m=""),"undefined"!==e(l)&&!1!==l&&""!==l&&SUI.slideModal(l,u,m),a.preventDefault()})),o.on("click",(function(e){SUI.closeModal(g),e.preventDefault()}))}(),this},SUI.modalDialog()}(jQuery)}},t={};function i(a){var o=t[a];if(void 0!==o)return o.exports;var s=t[a]={exports:{}};return e[a](s,s.exports,i),s.exports}i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,{a:t}),t},i.d=function(e,t){for(var a in t)i.o(t,a)&&!i.o(e,a)&&Object.defineProperty(e,a,{enumerable:!0,get:t[a]})},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){"use strict";var e;i(559),i(150);(e=jQuery)("#enable_history").on("change",(function(){e(this).is(":checked")?e("#history_records_ttl_control").show():e("#history_records_ttl_control").hide()})),e("#hw-show-usage-details").on("click",(function(t){t.preventDefault(),e.post(ajaxurl,{beforeSend(){e("#hw-usage-fetching").show(),e("#hwusage-modal-wrapper").empty()},action:"handywriter_usage_details",nonce:HandywriterAdmin.nonce},(function(t){e("#hw-usage-fetching").hide();const i=e("#hwusage-modal-wrapper");if(t.success)i.html(t.data.html);else{const e=`<div role="alert" id="inline-notice-general" class="sui-notice sui-notice-red sui-active" aria-live="assertive" style="display: block;">\n\t\t\t\t\t\t<div class="sui-notice-content">\n\t\t\t\t\t\t\t<div class="sui-notice-message">\n\t\t\t\t\t\t\t\t<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>\n\t\t\t\t\t\t\t\t<p>${t.data.message}</p></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>`;i.html(e)}}))}))}()}();