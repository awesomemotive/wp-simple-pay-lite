!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=80)}([function(e,t){e.exports=window.wp.element},,function(e,t){e.exports=window.wp.i18n},function(e,t){e.exports=window.wp.components},,,function(e,t,n){var r=n(26),o=n(27),c=n(22),a=n(28);e.exports=function(e,t){return r(e)||o(e,t)||c(e,t)||a()},e.exports.__esModule=!0,e.exports.default=e.exports},,,function(e,t){e.exports=window.wp.primitives},function(e,t){e.exports=window.wp.url},,function(e,t){e.exports=window.lodash},function(e,t,n){var r;!function(){"use strict";var n={}.hasOwnProperty;function o(){for(var e=[],t=0;t<arguments.length;t++){var r=arguments[t];if(r){var c=typeof r;if("string"===c||"number"===c)e.push(r);else if(Array.isArray(r)){if(r.length){var a=o.apply(null,r);a&&e.push(a)}}else if("object"===c)if(r.toString===Object.prototype.toString)for(var l in r)n.call(r,l)&&r[l]&&e.push(l);else e.push(r.toString())}}return e.join(" ")}e.exports?(o.default=o,e.exports=o):void 0===(r=function(){return o}.apply(t,[]))||(e.exports=r)}()},,,,,,function(e,t){function n(){return e.exports=n=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e},e.exports.__esModule=!0,e.exports.default=e.exports,n.apply(this,arguments)}e.exports=n,e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t){e.exports=window.wp.compose},function(e,t){e.exports=function(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r},e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t,n){var r=n(21);e.exports=function(e,t){if(e){if("string"==typeof e)return r(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?r(e,t):void 0}},e.exports.__esModule=!0,e.exports.default=e.exports},,,,function(e,t){e.exports=function(e){if(Array.isArray(e))return e},e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t){e.exports=function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,o,c,a,l=[],i=!0,s=!1;try{if(c=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;i=!1}else for(;!(i=(r=c.call(n)).done)&&(l.push(r.value),l.length!==t);i=!0);}catch(e){s=!0,o=e}finally{try{if(!i&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(s)throw o}}return l}},e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t,n){var r=n(34),o=n(35),c=n(22),a=n(36);e.exports=function(e){return r(e)||o(e)||c(e)||a()},e.exports.__esModule=!0,e.exports.default=e.exports},,,,,function(e,t,n){var r=n(21);e.exports=function(e){if(Array.isArray(e))return r(e)},e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t){e.exports=function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)},e.exports.__esModule=!0,e.exports.default=e.exports},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},e.exports.__esModule=!0,e.exports.default=e.exports},,,function(e,t){e.exports=window.wp.a11y},,function(e,t){e.exports=window.wp.keycodes},,,function(e,t,n){"use strict";var r=n(0);t.a=function(e){let{icon:t,size:n=24,...o}=e;return Object(r.cloneElement)(t,{width:n,height:n,...o})}},,,,,,,,,,,,,,,,,,,,,,function(e,t,n){"use strict";var r=n(0),o=n(9);const c=Object(r.createElement)(o.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(o.Path,{d:"M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"}));t.a=c},function(e,t,n){"use strict";var r=n(0),o=n(9);const c=Object(r.createElement)(o.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(o.Path,{d:"M13.5 6C10.5 6 8 8.5 8 11.5c0 1.1.3 2.1.9 3l-3.4 3 1 1.1 3.4-2.9c1 .9 2.2 1.4 3.6 1.4 3 0 5.5-2.5 5.5-5.5C19 8.5 16.5 6 13.5 6zm0 9.5c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"}));t.a=c},,function(e,t,n){"use strict";var r=n(0),o=n(9);const c=Object(r.createElement)(o.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(r.createElement)(o.Path,{d:"M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"}));t.a=c},,,,,,,,,,,function(e,t,n){"use strict";n.r(t);var r=n(6),o=n.n(r),c=n(0),a=n(3),l=n(41),i=n(10),s=n(2),u=simpayHelp.hasSeen,p=function(e){var t=e.onOpen,n=Object(c.useState)("0"===u),r=o()(n,2),l=r[0],i=r[1];return Object(c.createElement)("div",null,Object(c.createElement)("button",{type:"button",className:"simpay-branding-bar__actions-button",onClick:function(){i(!1),t()}},Object(c.createElement)("svg",{viewBox:"0 0 20 20",fill:"none",xmlns:"http://www.w3.org/2000/svg"},Object(c.createElement)("path",{fillRule:"evenodd",clipRule:"evenodd",d:"M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z",fill:"currentColor"}))),l&&Object(c.createElement)(a.Popover,{position:"top right",noArrow:!1},Object(c.createElement)("div",{className:"simpay-help-popover"},Object(c.createElement)("h4",null,Object(s.__)("Need help with something?","simple-pay")),Object(c.createElement)("p",null,Object(s.__)("Answers are at your fingertips with the WP Simple Pay help panel. Quickly view suggested articles, search for a specific feature, or submit a support ticket.","simple-pay")),Object(c.createElement)(a.Button,{variant:"secondary",isSecondary:!0,onClick:function(){return i(!1)}},Object(s.__)("Got it!","simple-pay")))))},m=n(13),f=n.n(m),b=n(20);function d(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=new Array(t);n<t;n++)r[n]=e[n];return r}function h(e,t){"function"==typeof e?e(t):e&&e.hasOwnProperty("current")&&(e.current=t)}var O=n(69),v=function(e){var t=e.onClose;return Object(c.createElement)("div",{className:"simpay-help-panel__header"},Object(c.createElement)("span",null,Object(s.__)("We're Here to Help","simple-pay")),Object(c.createElement)(a.Button,{icon:O.a,iconSize:20,onClick:t,label:Object(s.__)("Close help","simple-pay"),showTooltip:!1}))},y=n(44),j=n(9),w=Object(c.createElement)(j.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(c.createElement)(j.Path,{d:"M12.75 9.333c0 .521-.102.977-.327 1.354-.23.386-.555.628-.893.774-.545.234-1.183.227-1.544.222l-.12-.001v-1.5h.123c.414.001.715.002.948-.099a.395.395 0 00.199-.166c.05-.083.114-.253.114-.584V7.2H8.8V4h3.95v5.333zM7.95 9.333c0 .521-.102.977-.327 1.354-.23.386-.555.628-.893.774-.545.234-1.183.227-1.544.222l-.12-.001v-1.5h.123c.414.001.715.002.948-.099a.394.394 0 00.198-.166c.05-.083.115-.253.115-.584V7.2H4V4h3.95v5.333zM13 20H4v-1.5h9V20zM20 16H4v-1.5h16V16z"})),g=Object(c.createElement)(j.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(c.createElement)(j.Path,{fillRule:"evenodd",d:"M17.375 15.656A6.47 6.47 0 0018.5 12a6.47 6.47 0 00-.943-3.374l-1.262.813c.448.749.705 1.625.705 2.561a4.977 4.977 0 01-.887 2.844l1.262.813zm-1.951 1.87l-.813-1.261A4.976 4.976 0 0112 17c-.958 0-1.852-.27-2.613-.736l-.812 1.261A6.47 6.47 0 0012 18.5a6.47 6.47 0 003.424-.974zm-8.8-1.87A6.47 6.47 0 015.5 12c0-1.235.344-2.39.943-3.373l1.261.812A4.977 4.977 0 007 12c0 1.056.328 2.036.887 2.843l-1.262.813zm2.581-7.803A4.977 4.977 0 0112 7c1.035 0 1.996.314 2.794.853l.812-1.262A6.47 6.47 0 0012 5.5a6.47 6.47 0 00-3.607 1.092l.812 1.261zM12 20a8 8 0 100-16 8 8 0 000 16zm0-4.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z",clipRule:"evenodd"}));function _(e,t,n,r){return!1===function(e){return Object(i.getAuthority)(window.location.href)!==Object(i.getAuthority)(e)}(e)?e:Object(i.addQueryArgs)(e.replace(/\/?$/,"/"),{utm_source:"WordPress",utm_campaign:r?"lite-plugin":"pro-plugin",utm_medium:t,utm_content:n})}var E=simpayHelp.isLite,x=function(e){var t=e.searchTerm;return Object(c.createElement)("div",{className:"simpay-help-panel__footer"},Object(c.createElement)("div",{className:"simpay-help-panel__footer-action"},Object(c.createElement)(y.a,{icon:w,size:48}),Object(c.createElement)("h4",null,Object(s.__)("View Documentation","simple-pay")),Object(c.createElement)("p",null,Object(s.__)("Browse documentation, references, and tutorials for WP Simple Pay.","simple-pay")),Object(c.createElement)(a.Button,{variant:"secondary",isSecondary:!0,href:_("https://wpsimplepay.com/docs/","help",""===t?"View Documentation":t,"1"===E),target:"_blank"},Object(s.__)("View All Documentation","simple-pay"))),Object(c.createElement)("div",{className:"simpay-help-panel__footer-action"},Object(c.createElement)(y.a,{icon:g,size:48}),Object(c.createElement)("h4",null,Object(s.__)("Get Support","simple-pay")),Object(c.createElement)("p",null,Object(s.__)("Submit a ticket and our world class support team will be in touch soon.","simple-pay")),Object(c.createElement)(a.Button,{variant:"secondary",isSecondary:!0,href:"1"===E?"https://wordpress.org/support/plugin/stripe/":_("https://wpsimplepay.com/support","help",""===t?"Get Support":t,!1),target:"_blank"},Object(s.__)("Submit a Support Ticket","simple-pay"))))},S=n(66),C=n(67),M=function e(t){var n=t.className,r=t.onChange,o=t.value,l=t.label,i=t.placeholder,u=void 0===i?Object(s.__)("Search"):i,p=t.hideLabelFromVision,m=void 0===p||p,d=t.help,h=Object(b.useInstanceId)(e),O=Object(c.useRef)(),v="components-search-control-".concat(h);return Object(c.createElement)(a.BaseControl,{label:l,id:v,hideLabelFromVision:m,help:d,className:f()(n,"components-search-control")},Object(c.createElement)("div",{className:"components-search-control__input-wrapper"},Object(c.createElement)("input",{ref:O,className:"components-search-control__input",id:v,type:"search",placeholder:u,onChange:function(e){return r(e.target.value)},autoComplete:"off",value:o||""}),Object(c.createElement)("div",{className:"components-search-control__icon"},!!o&&Object(c.createElement)(a.Button,{icon:S.a,label:Object(s.__)("Reset search","simple-pay"),onClick:function(){r(""),O.current.focus()}}),!o&&Object(c.createElement)(y.a,{icon:C.a}))))},A=n(19),k=n.n(A),P=n(39),z=simpayHelp.isLite,N=function(e){var t=e.title,n=e.description,r=e.url,o=e.searchTerm;return Object(c.createElement)("div",{className:"simpay-help-panel__result"},Object(c.createElement)("a",{href:_(r,"help",o,"1"===z),target:"_blank",rel:"noreferrer"},t),n&&Object(c.createElement)("p",null,n))},V=n(12),L=Object(c.createElement)(j.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(c.createElement)(j.Path,{d:"M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"})),T=Object(c.createElement)(j.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},Object(c.createElement)(j.Path,{d:"M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"})),B=simpayHelp.isLite,H=function(e){var t=e.title,n=e.slug,r=e.docs,l=e.openCategory,i=e.setOpenCategory,u=Object(c.useState)(!1),p=o()(u,2),m=p[0],f=p[1],b=r.slice(0,5),d=m?r:b,h=l===n;return Object(c.createElement)("div",{className:"simpay-help-panel__category"},Object(c.createElement)("button",{className:"simpay-help-panel__category-title",onClick:function(){return i(h?"":n)}},Object(c.createElement)("span",null,t),Object(c.createElement)(y.a,{icon:h?L:T,size:32})),h&&Object(c.createElement)("div",null,d.map((function(e){var t=e.title,n=e.url;return Object(c.createElement)("a",{key:n,href:_(n,"help",t,"1"===B),target:"_blank",rel:"noreferrer"},t)})),!m&&r.length>5&&Object(c.createElement)(a.Button,{variant:"secondary",isSecondary:!0,isSmall:!0,onClick:function(){return f(!0)}},Object(s.__)("View all","simple-pay"))))},R=simpayHelp,I=R.docs,G=R.docsCategories,D=function(){var e,t=Object(c.useState)("getting-started"),n=o()(t,2),r=n[0],a=n[1];return Object(c.createElement)("div",{className:"simpay-help-panel__categories"},(e=Object(V.groupBy)(I,"categories"),Object(V.map)(e,(function(e,t){return{slug:t,title:G[t]||"",docs:e}}))).map((function(e){return Object(c.createElement)(H,k()({key:e.slug,openCategory:r,setOpenCategory:a},e))})))},F=n(29),W=n.n(F);function U(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return(e=(e=Object(V.deburr)(e)).replace(/^\//,"")).toLowerCase()}var Z=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return Object(V.words)(U(e))};function $(e,t){var n,r,o=e.title||"",c=e.excerpt||"",a=e.categories||[],l=e.tags||[],i=U(t),s=U(o),u=0;if(i===s)u+=30;else if(s.startsWith(i))u+=20;else{var p=[o,c].concat(W()(a),W()(l)).join(" ");0===(n=Object(V.words)(i),r=p,Object(V.differenceWith)(n,Z(r),(function(e,t){return t.includes(e)}))).length&&(u+=10)}return u}var Q=simpayHelp.docs,K=function(e){var t=e.searchTerm,n=Object(c.useMemo)((function(){var e=[];return""!==t&&(e=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",n=Z(t);if(0===n.length)return e;var r=e.map((function(e){return[e,$(e,t)]})).filter((function(e){return o()(e,2)[1]>0}));return r.sort((function(e,t){var n=o()(e,2)[1];return o()(t,2)[1]-n})),r.map((function(e){return o()(e,1)[0]}))}(Q,t)),e}),[t]),r=Object(b.useDebounce)(P.speak,500);Object(c.useEffect)((function(){if(t){var e=n.length,o=Object(s.sprintf)(/* translators: %d: number of results. */
Object(s._n)("%d result found.","%d results found.",e,"simple-pay"),e);r(o)}}),[t,r]);var a=!(null==n||!n.length);return Object(c.createElement)("div",{className:"simpay-help-panel__results"},!a&&Object(c.createElement)(D,null),a&&n.map((function(e){return Object(c.createElement)(N,k()({key:e.id,searchTerm:t},e))})))},q=function(e){var t=e.onClose,n=e.onSearch,r=e.searchTerm,o=Object(b.useFocusOnMount)("firstElement"),l=function(e){var t=Object(c.useRef)(),n=Object(c.useRef)(!1),r=Object(c.useRef)([]),o=Object(c.useRef)(e);return o.current=e,Object(c.useLayoutEffect)((function(){!1===n.current&&e.forEach((function(e,n){var o=r.current[n];e!==o&&(h(o,null),h(e,t.current))})),r.current=e}),e),Object(c.useLayoutEffect)((function(){n.current=!1})),Object(c.useCallback)((function(e){h(t,e),n.current=!0;var c,a=function(e,t){var n="undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(!n){if(Array.isArray(e)||(n=function(e,t){if(e){if("string"==typeof e)return d(e,void 0);var n=Object.prototype.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?d(e,void 0):void 0}}(e))||t&&e&&"number"==typeof e.length){n&&(e=n);var r=0,o=function(){};return{s:o,n:function(){return r>=e.length?{done:!0}:{done:!1,value:e[r++]}},e:function(e){throw e},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var c,a=!0,l=!1;return{s:function(){n=n.call(e)},n:function(){var e=n.next();return a=e.done,e},e:function(e){l=!0,c=e},f:function(){try{a||null==n.return||n.return()}finally{if(l)throw c}}}}(e?o.current:r.current);try{for(a.s();!(c=a.n()).done;)h(c.value,e)}catch(e){a.e(e)}finally{a.f()}}),[])}([Object(b.useConstrainedTabbing)(),Object(b.useFocusReturn)(),o]);return Object(c.createElement)(a.Animate,{type:"slide-in",options:{origin:"left"}},(function(e){var o=e.className,a=f()("simpay-help-panel",o);return Object(c.createElement)("div",{ref:l,className:a},Object(c.createElement)(v,{onClose:t}),Object(c.createElement)("div",{className:"simpay-help-panel__search"},Object(c.createElement)(M,{label:Object(s.__)("Search the documentation","simple-pay"),placeholder:Object(s.__)("Search","simple-pay"),onChange:n,value:r}),Object(c.createElement)(K,{searchTerm:r})),Object(c.createElement)(x,{searchTerm:r}))}))},J=function(e){var t=e.isOpen,n=e.onClose;return Object(c.useEffect)((function(){return document.body.classList.toggle("simpay-help-body-locked"),function(){document.body.classList.remove("simpay-help-body-locked")}}),[t]),Object(c.createElement)("button",{className:"simpay-help-backdrop",onClick:n})},X=simpayHelp,Y=X.docsSearchTerm,ee=X.docs;function te(e){var t=Object(i.getFragment)(e),n=Y;if(t&&t.includes("/")){var r=t.split("/");t=r[0],n=Object(i.safeDecodeURI)(r[1])}return{hash:t,searchTerm:n}}Object(c.render)(Object(c.createElement)((function(){var e=te(window.location.href),t=e.hash,n=e.searchTerm,r=Object(c.useState)(n),i=o()(r,2),s=i[0],u=i[1],m=Object(c.useState)("#help"===t),f=o()(m,2),b=f[0],d=f[1];function h(){d(!1),window.history.pushState("",document.title,window.location.pathname+window.location.search)}return Object(c.useEffect)((function(){function e(e){var t=te(e.newURL),r=t.hash,o=t.searchTerm;"#help"===r&&d(!0),o!==n&&u(o)}return window.addEventListener("hashchange",e),function(){window.removeEventListener("hashchange",e)}}),[]),Object(c.createElement)("div",{onKeyDown:function(e){e.keyCode!==l.ESCAPE||e.defaultPrevented||(e.preventDefault(),d(!1))},role:"region"},Object(c.createElement)(p,{isOpen:b,onOpen:function(){u(Y),d(!0),window.history.pushState("",document.title,window.location.pathname+window.location.search+"#help")}}),b&&Object(c.createElement)(c.Fragment,null,Object(c.createElement)(q,{onClose:h,onSearch:u,searchTerm:s,docs:ee}),Object(c.createElement)(J,{onClose:h})),Object(c.createElement)(a.Popover.Slot,null))}),null),document.getElementById("simpay-branding-bar-help"))}]);