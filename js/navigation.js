"use strict";var MENUTOGGLE=document.querySelector(".main-navigation .menu-toggle"),SITENAV=document.querySelector(".main-navigation");function subNavToggle(e){e.setAttribute("aria-expanded","false"===e.getAttribute("aria-expanded")?"true":"false"),e.classList.toggle("toggled-on"),e.nextElementSibling.classList.toggle("toggled-on");var t=e.querySelector(".screen-reader-text");t.textContent=wprigScreenReaderText.expand===t.textContent?wprigScreenReaderText.collapse:wprigScreenReaderText.expand}function initMainNavigation(){SITENAV.addEventListener("click",function(e){e.target.classList.contains("dropdown-toggle")&&subNavToggle(e.target)},!1)}function initMenuToggle(){void 0!==MENUTOGGLE&&(MENUTOGGLE.setAttribute("aria-expanded","false"),MENUTOGGLE.addEventListener("click",function(){SITENAV.classList.toggle("toggled-on"),MENUTOGGLE.setAttribute("aria-expanded","false"===MENUTOGGLE.getAttribute("aria-expanded")?"true":"false")},!1))}initMainNavigation(),initMenuToggle();