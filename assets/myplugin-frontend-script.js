(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

jQuery(function ($) {
  //Every tiny tools are implemented  in gakpHelper as global object literal.
  var ajaxHelper = {
    ajax: function ajax(data) {
      return jQuery.post(myplugin_frontend_script.ajax_url, data); //Ajax url,Data
    }
  };
  $(document).ready(function () {
    if (myplugin_frontend_script.use_for == "affiliate") {
      if (myplugin_frontend_script.button_action == "redirect" || myplugin_frontend_script.button_action == "details") {
        //Imported product redirection to amazon
        $(document).on("click", ".add_to_cart_button", function (e) {
          e.preventDefault();
          var productType = "";
          $(this).hasClass("product_type_variable") ? productType = "variable" : productType = "simple"; // console.log(productType);

          if ($(this).attr("data-product_id")) {
            var product_id = $(this).attr("data-product_id");
            var data = {
              action: "aadp_imported_product_redirect_to_amzon",
              product_id: product_id,
              product_type: productType
            };
            var request = ajaxHelper.ajax(data);
            request.done(function (response) {
              // return
              if (response !== "") {
                window.location.href = response;
              } else {
                alert("Error occured, Contact support!");
              }
            }).fail(function (xhr, status, error) {
              //Ajax request failed.
              alert("Error occured, Contact support!");
            });
          } else {
            alert("Error occured, Contact support!");
          }
        });
        $(document).on("click", ".single_add_to_cart_button", function (e) {
          e.preventDefault();
          var productType = "simple";
          var productId = "";

          if ($(this).parent().find(".variation_id").length > 0) {
            productType = "variable";
            productId = $(this).parent().find("input[name=product_id]").val();
          } else {
            productType = "simple";
            productId = $(this).val();
          }

          if (productId != "") {
            var data = {
              action: "aadp_imported_product_redirect_to_amzon",
              product_id: productId,
              product_type: productType
            };
            var request = ajaxHelper.ajax(data);
            request.done(function (response) {
              if (response !== "") {
                window.location.href = response;
              } else {
                alert("Error occured, Contact support!");
              }
            }).fail(function (xhr, status, error) {
              //Ajax request failed.
              alert("Error occured, Contact support!");
            });
          } else {
            alert("Error occured, Contact support!");
          }
        });
      }

      $(document).on("click", ".checkout-button", function (e) {
        e.preventDefault();
        $(this).append("<p>Processing...</p>");
        var data = {
          action: "aadp_cart_to_amazon_cart_url"
        };
        var request = ajaxHelper.ajax(data);
        request.done(function (response) {
          for (var i = 0; i < response.length; i++) {
            window.open(response[i], "_blank");
          }
        });
      });
    }

    var store;
    $("#aadp-search-input").on("keypress", function (e) {
      // $('.aadp-loader').show();
      if (e.which == 13 || e.keyCode == 13) {
        e.preventDefault();
        var keyWord = $(this).val().trim();

        if (keyWord != "") {
          store = $("#store-country").val();
          var department = $("#department").val();
          var page = 1;
          var url = store + "s?k=" + keyWord + "&i=" + department;
          aadp_search_products(url, store);
        }
      }
    });
    $("#aadp-search-btn").on("click", function (e) {
      e.preventDefault();
      var keyWord = $("#aadp-search-input").val().trim();

      if (keyWord != "") {
        store = $("#store-country").val();
        var department = $("#department").val();
        var url = store + "s?k=" + keyWord + "&i=" + department;
        aadp_search_products(url, store);
      }
    });
    $(document).on("change", "#store-country", function (e) {
      e.preventDefault();
      $("#department").hide();
      $(".buttonload").remove();
      $("#department").after('<button class="buttonload"><i class="fa fa-spinner fa-spin"></i>Loading</button>');
      store = $(this).val();
      var data = {
        store: store,
        action: "get_department"
      };
      var request = ajaxHelper.ajax(data);
      request.done(function (response) {
        // console.log(response);
        $("#department").empty();
        $(".buttonload").remove();
        $("#department").append(response);
        $("#department").show();
      });
    });
    var variation;
    $(document).on("click", ".aadp-affiliate-view-details", function (e) {
      e.preventDefault();
      $("#exampleModalCenter").remove();
      var btn = $(this);
      var product = btn.attr("data-product-link");
      var productTitle = btn.parents(".card").children(".card-body").children(".card-title").text();
      var productImg = btn.parents(".card").children("a").children("img").attr("src");
      var data = {
        nonce: myplugin_frontend_script.nonce,
        action: "aadp_affiliate_view_details",
        request: product,
        title: productTitle,
        img: productImg
      }; // console.log(data);

      var request = ajaxHelper.ajax(data);
      request.done(function (response) {
        // console.log(response);
        variation = response.variation;
        btn.after(response.template); // $('.aadp-loader').hide();

        $("#exampleModalCenter").modal("show");
        $(".xzoom, .xzoom-gallery").xzoom({
          tint: "#333",
          Xoffset: 15
        });
      }).fail(function (jqXHR, textStatus) {
        // console.log(response);
        // console.log(jqXHR);
        // console.log(textStatus);
        if (textStatus == "error") {
          var modal = '<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">' + '          <div class="modal-dialog modal-dialog-centered" role="document">' + '            <div class="modal-content">' + '              <div class="modal-header">' + '                <h5 class="modal-title" id="exampleModalLongTitle"></h5>' + '                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">' + // '                  <span aria-hidden="true">×</span>' +
          "                </button>" + "              </div>" + '              <div class="modal-body">' + '                <p">Currently unavailable' + "                  " + "                </p>" + "              </div>" + "            </div>" + "          </div>" + "        </div>";
          $("body").append(modal);
          $("#exampleModalCenter").modal("show");
        }
      });
    });
    var asin;
    $(document).on("change", ".attribute", function (e) {
      var select = $(".attribute");
      var selectedVariation = [];

      for (var i = 0; i < select.length; i++) {
        selectedVariation.push(parseInt($(select[i]).val()));
      } // console.log(selectedVariation);
      // console.log(selectedVariation.includes(NaN));


      var checkVariation = selectedVariation.includes(NaN);

      if (checkVariation == false) {
        asin = getKeyselectedVariation(variation, selectedVariation); // console.log(asin);

        if (asin != false) {
          var variationLink = store + "dp/" + asin + "?th=1&psc=1";
          $("#sendRequestBtn").attr("data-product-link", variationLink);
          var data = {
            nonce: myplugin_frontend_script.nonce,
            action: "change_variation",
            variationLink: variationLink
          }; // console.log(data);

          var request = ajaxHelper.ajax(data);
          request.done(function (response) {
            // console.log(response);
            $("#tabbed_image_gallery").empty();
            $("#variation-price").empty();
            $("#tabbed_image_gallery").append(response.galleryTab);
            $(".xzoom, .xzoom-gallery").xzoom({
              tint: "#333",
              Xoffset: 15
            });
            $(".aadp-availability").empty();
            $("#variation-price").empty();
            $("#variation-price").append(response.price);
          });
        } else {
          $(".aadp-availability").empty();
          $(".aadp-availability").append("<span class='text-danger'>Currently Unavailable</span>");
        }
      }
    });

    var getKeyselectedVariation = function getKeyselectedVariation(obj, selectedVariation) {
      var returnKey = false;
      $.each(obj, function (key, info) {
        if (arraysEqual(info, selectedVariation) == true) {
          returnKey = key;
          return false;
        }
      });
      return returnKey;
    };

    function arraysEqual(a1, a2) {
      /* WARNING: arrays must not contain {objects} or behavior may be undefined */
      return JSON.stringify(a1) == JSON.stringify(a2);
    }

    $(document).on("click", "#aadp-add-to-cart", function (e) {
      e.preventDefault();
      var site_tag = $(this).attr("aadp-affiliate-tag");
      var quantity = $("#quantity").val();

      if ($(".attribute").length > 0) {
        if (asin != false) {
          var amazonCartLink = store + "/gp/aws/cart/add.html?AssociateTag=" + site_tag + "&ASIN.1=" + asin + "&Quantity.1=" + quantity;
          window.open(amazonCartLink, "_blank");
        }
      } else {
        var singleProductAsin = $(this).attr("data-asin");
        var amazonCartLink = store + "/gp/aws/cart/add.html?AssociateTag=" + site_tag + "&ASIN.1=" + singleProductAsin + "&Quantity.1=" + quantity;
        window.open(amazonCartLink, "_blank");
      }
    }); // var store;

    function aadp_search_products(url, store) {
      var req_data = {
        nonce: myplugin_frontend_script.nonce,
        action: "search_product",
        store: store,
        url: url
      }; // console.log(req_data);

      var request = ajaxHelper.ajax(req_data);
      request.done(function (response) {
        // console.log(response);
        $("#aadp-import-product").empty();
        $("#aadp-import-product").append(response.data);
        $("html,body").animate({
          scrollTop: $("#aadp-import-product").offset().top
        }, "slow");
      });
    }

    if ($(".aadp_searc_amazon")) {
      $(".aadp_searc_amazon").parent().removeClass("entry-content").addClass("container");
    }
  });
});

},{}]},{},[1]);

//# sourceMappingURL=myplugin-frontend-script.js.map
