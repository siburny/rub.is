this.wp=this.wp||{},this.wp.a11y=function(t){var n={};function e(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,e),o.l=!0,o.exports}return e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.r=function(t){Object.defineProperty(t,"__esModule",{value:!0})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,n){return Object.prototype.hasOwnProperty.call(t,n)},e.p="",e(e.s=416)}({116:function(t,n,e){"use strict";var r=e(38),o=e(50),i=e(41),u=e(67),c=e(37);t.exports=function(t,n,e){var a=c(t),f=e(u,a,""[t]),p=f[0],s=f[1];i(function(){var n={};return n[a]=function(){return 7},7!=""[t](n)})&&(o(String.prototype,t,p),r(RegExp.prototype,a,2==n?function(t,n){return s.call(t,this,n)}:function(t){return s.call(t,this)}))}},26:function(t,n){var e=t.exports="undefined"!=typeof window&&window.Math==Math?window:"undefined"!=typeof self&&self.Math==Math?self:Function("return this")();"number"==typeof __g&&(__g=e)},33:function(t,n,e){t.exports=!e(41)(function(){return 7!=Object.defineProperty({},"a",{get:function(){return 7}}).a})},37:function(t,n,e){var r=e(93)("wks"),o=e(62),i=e(26).Symbol,u="function"==typeof i;(t.exports=function(t){return r[t]||(r[t]=u&&i[t]||(u?i:o)("Symbol."+t))}).store=r},38:function(t,n,e){var r=e(43),o=e(74);t.exports=e(33)?function(t,n,e){return r.f(t,n,o(1,e))}:function(t,n,e){return t[n]=e,t}},39:function(t,n){t.exports=function(t){return"object"==typeof t?null!==t:"function"==typeof t}},409:function(t,n){!function(){t.exports=this.wp.domReady}()},41:function(t,n){t.exports=function(t){try{return!!t()}catch(t){return!0}}},416:function(t,n,e){"use strict";e.r(n);var r=function(t){t=t||"polite";var n=document.createElement("div");return n.id="a11y-speak-"+t,n.className="a11y-speak-region",n.setAttribute("style","position: absolute;margin: -1px;padding: 0;height: 1px;width: 1px;overflow: hidden;clip: rect(1px, 1px, 1px, 1px);-webkit-clip-path: inset(50%);clip-path: inset(50%);border: 0;word-wrap: normal !important;"),n.setAttribute("aria-live",t),n.setAttribute("aria-relevant","additions text"),n.setAttribute("aria-atomic","true"),document.querySelector("body").appendChild(n),n},o=function(){for(var t=document.querySelectorAll(".a11y-speak-region"),n=0;n<t.length;n++)t[n].textContent=""},i=e(409),u=e.n(i),c=(e(64),""),a=function(t){return t=t.replace(/<[^<>]+>/g," "),c===t&&(t+=" "),c=t,t};e.d(n,"setup",function(){return f}),e.d(n,"speak",function(){return p});var f=function(){var t=document.getElementById("a11y-speak-polite"),n=document.getElementById("a11y-speak-assertive");null===t&&(t=r("polite")),null===n&&(n=r("assertive"))};u()(f);var p=function(t,n){o(),t=a(t);var e=document.getElementById("a11y-speak-polite"),r=document.getElementById("a11y-speak-assertive");r&&"assertive"===n?r.textContent=t:e&&(e.textContent=t)}},43:function(t,n,e){var r=e(51),o=e(87),i=e(79),u=Object.defineProperty;n.f=e(33)?Object.defineProperty:function(t,n,e){if(r(t),n=i(n,!0),r(e),o)try{return u(t,n,e)}catch(t){}if("get"in e||"set"in e)throw TypeError("Accessors not supported!");return"value"in e&&(t[n]=e.value),t}},47:function(t,n){var e={}.hasOwnProperty;t.exports=function(t,n){return e.call(t,n)}},50:function(t,n,e){var r=e(26),o=e(38),i=e(47),u=e(62)("src"),c=Function.toString,a=(""+c).split("toString");e(63).inspectSource=function(t){return c.call(t)},(t.exports=function(t,n,e,c){var f="function"==typeof e;f&&(i(e,"name")||o(e,"name",n)),t[n]!==e&&(f&&(i(e,u)||o(e,u,t[n]?""+t[n]:a.join(String(n)))),t===r?t[n]=e:c?t[n]?t[n]=e:o(t,n,e):(delete t[n],o(t,n,e)))})(Function.prototype,"toString",function(){return"function"==typeof this&&this[u]||c.call(this)})},51:function(t,n,e){var r=e(39);t.exports=function(t){if(!r(t))throw TypeError(t+" is not an object!");return t}},62:function(t,n){var e=0,r=Math.random();t.exports=function(t){return"Symbol(".concat(void 0===t?"":t,")_",(++e+r).toString(36))}},63:function(t,n){var e=t.exports={version:"2.5.7"};"number"==typeof __e&&(__e=e)},64:function(t,n,e){e(116)("replace",2,function(t,n,e){return[function(r,o){"use strict";var i=t(this),u=void 0==r?void 0:r[n];return void 0!==u?u.call(r,i,o):e.call(String(i),r,o)},e]})},67:function(t,n){t.exports=function(t){if(void 0==t)throw TypeError("Can't call method on  "+t);return t}},74:function(t,n){t.exports=function(t,n){return{enumerable:!(1&t),configurable:!(2&t),writable:!(4&t),value:n}}},79:function(t,n,e){var r=e(39);t.exports=function(t,n){if(!r(t))return t;var e,o;if(n&&"function"==typeof(e=t.toString)&&!r(o=e.call(t)))return o;if("function"==typeof(e=t.valueOf)&&!r(o=e.call(t)))return o;if(!n&&"function"==typeof(e=t.toString)&&!r(o=e.call(t)))return o;throw TypeError("Can't convert object to primitive value")}},81:function(t,n,e){var r=e(39),o=e(26).document,i=r(o)&&r(o.createElement);t.exports=function(t){return i?o.createElement(t):{}}},87:function(t,n,e){t.exports=!e(33)&&!e(41)(function(){return 7!=Object.defineProperty(e(81)("div"),"a",{get:function(){return 7}}).a})},92:function(t,n){t.exports=!1},93:function(t,n,e){var r=e(63),o=e(26),i=o["__core-js_shared__"]||(o["__core-js_shared__"]={});(t.exports=function(t,n){return i[t]||(i[t]=void 0!==n?n:{})})("versions",[]).push({version:r.version,mode:e(92)?"pure":"global",copyright:"© 2018 Denis Pushkarev (zloirock.ru)"})}});