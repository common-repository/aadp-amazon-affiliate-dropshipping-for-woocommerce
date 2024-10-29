(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

/*
	
    Ajax Example - JavaScript for Admin Area
	
*/
(function ($) {
  $(document).ready(function () {
    //Multiple Tags
    $(document).on("click", "#aadp-affiliate-tag-btn-add", function (e) {
      var tagLength = $(".aadp-affiliate-tag-pair").length;
      var piarDom = '<div class="aadp-affiliate-tag-pair">\n' + '<select name="amazon_associate_tag[' + tagLength + '][site]">\n' + '<option value="https://www.amazon.com.au/"> Australia </option>' + '<option value="https://www.amazon.com.br/"> Brazil </option>' + '<option value="https://www.amazon.ca/">Canada </option>' + '<option value="https://www.amazon.cn/"> China </option>' + '<option value="https://www.amazon.fr/">France </option>' + '<option value="https://www.amazon.de/"> Germany </option>' + '<option value="https://www.amazon.in/"> India </option>' + '<option value="https://www.amazon.it/"> Italy </option>' + '<option value="https://www.amazon.co.jp/"> Japan </option>' + '<option value="https://www.amazon.com.mx/"> Mexico </option>' + '<option value="https://www.amazon.nl/"> Netherland </option>' + '<option value="https://www.amazon.pl/"> Poland </option>' + '<option value="https://www.amazon.sa/"> Saudi Arabia </option>' + '<option value="https://www.amazon.sg/"> Singapore </option>' + '<option value="https://www.amazon.es/"> Spain < option>' + '<option value="https://www.amazon.se/"> Sweden </option>' + '<option value="https://www.amazon.com.tr/"> Turkey </option>' + '<option value="https://www.amazon.ae/"> United Arab Emirates </option>' + '<option value="https://www.amazon.co.uk/"> United Kingdom </option>' + '<option value="https://www.amazon.com/" selected>United States </option>\n' + "</select>\n" + '<input type="text" name="amazon_associate_tag[' + tagLength + '][tag]" placeholder="Associate ID">\n' + ' <button class="button aadp-affiliate-tag-btn-remove" type="button">X</button></div>';

      if (tagLength <= 19) {
        $(".aadp-affiliate-tags-wrapper").append(piarDom);
      }
    });
    $(document).on("click", ".aadp-affiliate-tag-btn-remove", function (e) {
      if (confirm("Are you sure to delete this Site & Tag both?")) {
        $(this).parent().remove();
      }
    });
  });
})(jQuery);

},{}]},{},[1]);

//# sourceMappingURL=ajax-admin.js.map
