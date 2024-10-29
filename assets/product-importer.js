(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

jQuery(function ($) {
  //Every tiny tools are implemented  in gakpHelper as global object literal.
  var ajaxHelper = {
    ajax: function ajax(data) {
      return jQuery.post(product_importer_script.ajax_url, data); //Ajax url,Data
    }
  };
  $(document).ready(function () {
    var processing = false;
    var store;
    var total_page = '';
    $(document).on("change", ".aadp-country-store", function (e) {
      e.preventDefault();
      $(".aadp-department").hide();
      $(".buttonload").remove();
      $(".aadp-department").after('<button class="buttonload"><i class="fa fa-spinner fa-spin"></i>Loading</button>');
      store = $(this).val();
      var data = {
        store: store,
        action: "aadp_get_department"
      };
      var request = ajaxHelper.ajax(data);
      request.done(function (response) {
        $(".aadp-department").empty();
        $(".aadp-department").append(response);
        $(".buttonload").remove();
        $(".aadp-department").show();
      });
    });
    $(document).on("click", ".aadp-pagination > a.page-numbers", function (e) {
      e.preventDefault();
      var url = $(this).attr("href");
      var urlObject = new URL(url);
      var page = urlObject.searchParams.get("page");
      total_page = $("#total-pages").val();
      aadp_search_for_import_products(url, page, store);
    });
    $(document).on("click", ".aadp-search", function (e) {
      e.preventDefault();
      var keyWord = $(".aadp-keyword").val().trim();

      if (keyWord != "") {
        store = $(".aadp-country-store").val();
        var department = $(".aadp-department").val();
        var page = 1;
        var url = store + "s?k=" + keyWord + "&i=" + department + "&page=" + page;
        aadp_search_for_import_products(url, page, store);
      }
    });
    $(document).on("click", ".aadp-cargo", function (e) {
      e.preventDefault();
      var btn = $(this);
      btn.attr("disabled", true);
      aadp_add_to_cargo(btn);
    });
    $(document).on("click", ".aadp-cargo-remove", function (e) {
      $(this).closest("li").remove();
      $('#aadp-cargo-counter').text(+$('#aadp-cargo-counter').text() - 1);
      var req_data = {
        nonce: product_importer_script.nonce,
        action: "aadp_remove_to_cargo",
        cargo_key: $(this).closest('li').attr('data-cargo-key')
      };
      var request = ajaxHelper.ajax(req_data);
      request.done(function (response) {
        aadp_check_empty_cargo();
      });
    }); // form-check-input

    $(document).on("click", "#aadp-add-all", function (e) {
      e.preventDefault();
      $(this).attr("disabled", true);
      $(".aadp-cargo").each(function (idx, el) {
        var thisBtn = $(this);
        thisBtn.attr("disabled", true);
        aadp_add_to_cargo(thisBtn);
      });
    });
    $(document).on("change", "#aadp-existing-category", function (e) {
      e.preventDefault();

      if (this.checked && $('#aadp-xc').length == false) {
        var selector = $(this);
        var req_data = {
          nonce: product_importer_script.nonce,
          action: "aadp_get_existing_category"
        };
        var request = ajaxHelper.ajax(req_data);
        request.done(function (response) {
          if ($('#aadp-nc').length == true) {
            $('#aadp-nc').remove();
          }

          selector.parent().after(response.data);
        });
      }
    });
    $(document).on("change", "#create-new-category", function (e) {
      e.preventDefault();

      if (this.checked && $('#aadp-nc').length == false) {
        if ($('#aadp-xc').length == true) {
          $('#aadp-xc').remove();
        }

        $(this).parent().after('<div class="form-group" id ="aadp-nc">' + '<input type="text" class="form-control" id="aadp-category" placeholder="Create category">' + '</div>');
      }
    });
    var report = [];
    $(document).on("click", "#importShortCode", function (e) {
      e.preventDefault(); // console.log(navigator.userAgent);

      $('#aadp-operation').remove();
      processing = true;
      var loaderPercent = 0;
      var bar1 = new ldBar("#myItem1");
      /* ldBar stored in the element */

      var bar2 = document.getElementById('myItem1').ldBar;
      $('#myItem1').show();
      bar1.set(loaderPercent);
      var importUrls = [];
      $(".aadp-import-cargo-url").each(function (idx, el) {
        importUrls[idx] = {};
        importUrls[idx].title = $(this).attr("data-product-title").trim();
        importUrls[idx].url = $(this).attr("data-product-url");
        importUrls[idx].store = $(this).attr("data-store");
        importUrls[idx].cargo_key = $(this).attr("data-cargo-key");
        importUrls[idx].type = $(this).attr("data-type");
      });
      var categoryType = $("input[name='aadp-category-type']:checked").val();
      var categoryVal = $('#aadp-category').val();
      var proCount = importUrls.length; // console.log("product :" + proCount);

      var ajaxRequestCount = proCount; // console.log(ajaxRequestCount);

      var proPercent = 100 / ajaxRequestCount * proCount;
      var perProPercent = proPercent / proCount;

      if (Array.isArray(importUrls) && importUrls.length) {
        var i = 0;
        report.product = [];
        $.each(importUrls, function (indexInArray, valueOfElement) {
          i++;
          valueOfElement.cat_type = categoryType;
          valueOfElement.cat_val = categoryVal;
          var req_data = {
            nonce: product_importer_script.nonce,
            action: "aadp_cargo_import",
            import_data: valueOfElement
          }; // console.log(req_data);

          productPromise(req_data).then(function (response) {
            // console.log(response);
            report.product[indexInArray] = [];
            report.product[indexInArray].message = response.data.message; // console.log("p:" + perProPercent);

            loaderPercent += perProPercent;
            bar1.set(loaderPercent); // console.log(response);

            $("ul").find("[data-cargo-key='".concat(response.data.cargo_key, "']")).remove();
            $('#aadp-cargo-counter').text(+$('#aadp-cargo-counter').text() - 1); //aadp_check_empty_cargo();

            if (i == importUrls.length) {
              if (parseInt(loaderPercent) == 100) {
                // console.log("uu" + loaderPercent);
                processing = false; // console.log(report);

                $('#myItem1').hide();
                bar1.set(0);
                aadp_check_empty_cargo();
                $('.aadp_loader').append('<div id ="aadp-operation"><div class="row"><div class="col-7"><p class="alert alert-success aadp-operation center">Hurray!! Job Completed.</p></div><div class="col-5"><p id="aadp-import-report" class="btn btn-info ">View Report</p><div></div> </div>');
              }
            }
          })["catch"](function (error) {
            console.log(error);
          });
        });
      }
    });
    $(document).on("click", "#aadp-import-report", function (e) {
      e.preventDefault();
      $("#reportModalCenter").remove(); // console.log(report);

      var reportMessage = '';
      reportMessage += '<div class="modal fade" id="reportModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"' + '    aria-hidden="true">' + '    <div class="modal-dialog modal-dialog-centered" role="document">' + '        <div class="modal-content modal-design">' + '            <div class="modal-header">' + '                <h5 class="modal-title aadp_modal_title aadp_modal_title_design" id="exampleModalLongTitle">Import Report</h5>' + '                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">' + // '                    <span aria-hidden="true">×</span>' +
      '                </button>' + '            </div>' + '            <div class="modal-body">';
      reportMessage += '<p class="product_report_title"><strong>Product Report:</strong></p>';
      reportMessage += '<ul>';
      $.each(report.product, function (indexInArray, valueOfElement) {
        // console.log(valueOfElement.shortcode);
        reportMessage += ' <li class="product_report_title">' + valueOfElement.message + ' </li>';
      });
      reportMessage += '</ul>' + '            </div>' + '        </div>' + '    </div>' + '</div>';
      $(this).after(reportMessage);
      $("#reportModalCenter").modal("show"); // console.log(reportMessage);
    });
    $(document).on("click", "a", function (e) {
      // console.log(processing);
      if (processing == true) {
        confirm("Product Importing operation is processing! Are you sure you want to leave?");
      }
    }); // btn btn-sm btn-success

    $(document).on("click", ".aadp-product-research", function (e) {
      e.preventDefault();
      $("#researchModalCenter").remove();
      var thisBtn = $(this);
      var url = thisBtn.attr('data-product-link');
      var req_data = {
        nonce: product_importer_script.nonce,
        action: 'aadp_product_research',
        url: url
      }; // console.log(req_data);

      var request = ajaxHelper.ajax(req_data);
      request.done(function (response) {
        // console.log(response.data);
        thisBtn.after(response.data);
        $("#researchModalCenter").modal("show");
      });
    });

    if ($('#aadp-import-product').length == true) {
      window.onscroll = function () {
        myFunction();
      };
    }

    function aadp_check_empty_cargo() {
      if ($('.aadp-import-cargo-url').length == false) {
        // console.log('')
        $('.shopping-cart-items').append('<li class="aadp-empty-cargo"><h6>Your cargo is currently empty.</h6></li>');
      }
    }

    function myFunction() {
      var header = document.getElementById("aadp-gol");
      var sticky = header.offsetTop;

      if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
      } else {
        header.classList.remove("sticky");
      }
    }

    (function () {
      $("#cart").on("click", function () {
        $("#aadp-cargo-box").fadeToggle("fast");
      });
    })();

    function aadp_search_for_import_products(url, page, store) {
      var req_data = {
        nonce: product_importer_script.nonce,
        action: "product_find",
        store: store,
        url: url,
        page: page,
        total_page: total_page
      };
      var request = ajaxHelper.ajax(req_data);
      request.done(function (response) {
        // console.log(response);
        $("#aadp-import-product").empty();
        $("#aadp-import-product").append(response.data);
        $("html,body").animate({
          scrollTop: $("#aadp-gol").offset().top
        }, "slow");
      });
    }

    function aadp_add_to_cargo(btn) {
      var title = btn.attr("data-product-title");
      var img = btn.attr("data-product-img");
      var url = btn.attr("data-product-link");
      var store = btn.attr("data-product-store");
      var type = btn.parents(".card-body").find(".aadp-type").val();
      var req_data = {
        nonce: product_importer_script.nonce,
        action: "aadp_store_cargo",
        title: title,
        img: img,
        url: url,
        type: type,
        store: store
      };
      var request = ajaxHelper.ajax(req_data);
      request.done(function (response) {
        if ($(".aadp-empty-cargo").length > 0) {
          $(".aadp-empty-cargo").remove();
        }

        var html = ' <li class="clearfix aadp-import-cargo-url" data-product-title="' + title + '" data-product-url="' + url + '" data-type="' + type + '" data-cargo-key="' + response.data + '"data-store="' + store + '">' + '<div class="row">' + '<div class="col-2 ">' + '<img src="' + img + '" alt="item1" />' + '</div>' + '<div class="col-8 ">';
        html += '<span class="item-name">' + title + '</span>' + '</div>' + '<div class="col-2">' + '<button class="btn btn-sm btn-danger aadp-cargo-remove">x</button>' + '</div>';
        '</div>';
        html += "</li>";
        $(".shopping-cart-items").append(html);
        var counter = +$('#aadp-cargo-counter').text() + 1;
        $('#aadp-cargo-counter').text(counter);
      });
    }
  });

  var productPromise = function productPromise(req_data) {
    return new Promise(function (resolve, reject) {
      var request = ajaxHelper.ajax(req_data);
      request.done(function (response) {
        resolve(response); // console.log(response);
      });
    });
  };
});

},{}]},{},[1]);

//# sourceMappingURL=product-importer.js.map
