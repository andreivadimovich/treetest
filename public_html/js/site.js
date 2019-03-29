$(document).ready(function() {

    // validate before submit forms
    var validate_error = 'Please fill all fields carefully (allowed characters is a-zA-Z0-9 and dash)';
    var validate_price_error = 'Enter the number with two digits';

    // category add
    $('#category_add').submit(function(e) {
        var name = $(this).find('input[name="title"]');

        if (name.val() == '' || name.val() == undefined ||
            (/^[a-zA-Z0-9 ]*$/.test(name.val()) == false)) {
            name.css('border', '2px solid red');
            alert(validate_error);
            return false;
        }
        return true;

        e.preventDefault();
    });

    // product submit
    $('#product_add').submit(function(e) {

        var name = $(this).find('input[name="title"]');
        var url = $(this).find('input[name="url"]');
        var price = $(this).find('input[name="price"]');

        if (name.val() == '' || name.val() == undefined ||
            (/^[a-zA-Z0-9 ]*$/.test(name.val()) == false)) {
            name.css('border', '2px solid red');
            alert(validate_error);
            return false;
        }

        if (url.val() !== "" && url.val() !== undefined && isURL(url.val()) == false) {
            url.css('border', '2px solid red');
            alert(validate_error);
            return false;
        }

        if (price.val() !== "" && price.val() !== undefined) {
            if (isFloat(price.val()) === false) {
                price.css('border', '2px solid red');
                alert(validate_price_error);
                return false;
            }
        }

        return true;

        e.preventDefault();
    });


    // ajax functions
    function deleteProduct(id) {
        $.get("/?delete=true&type=product&id="+id, function(data) { console.log(data); });
    }

    function deleteCategory(id) {
        $.get("/?delete=true&type=category&id="+id, function(data) { console.log(data); });
    }

    function update(id, new_name, type) {
        $.get("/?update=true&type="+type+"&id="+id+"&new_name="+new_name, function(data) { console.log(data); });
    }

    $(function() {
        // empty catalog
        if ($('select[name="subcategory"]:last option').length == 1 &&
            $('select[name="subcategory"]:last option').val() == "0") {
            $('#product_add, #catalog_block').css('display', 'none');
        }

        // plugin init
        $('#jstree').jstree({
            'core' : {
                "multiple" : false,
                "themes" : {
                    "dots" : true
                },
                "check_callback" : function (operation, node, parent, position, more) {
                    console.log(operation+' text : '+node.text+' id : '+node.data);

                        var type = '';
                        if (node.data.indexOf('product') !== -1) {
                            type = 'product';
                        } else {
                            type = 'category';
                        }

                        switch(operation) {
                            case 'delete_node':
                                console.log(node.data);

                                if (type == 'product') {
                                    var product_id = node.data.replace(/[^.\d]+/g,"");
                                    var delete_state = deleteProduct(product_id);
                                    alert('The product deleted');
                                } else if (type == 'category') {
                                    var category_id = node.data;
                                    var delete_state = deleteCategory(category_id);
                                    alert('The category deleted');
                                }
                                break;


                            case 'rename_node':
                                setTimeout(function() {
                                    state = update(node.data.replace(/[^.\d]+/g,""), node.text, type);
                                },0.1)
                                break;


                            default:
                                break;
                        }
                    return true;
                },

                'data': {
                    "url": "http://127.0.0.1/?all=true",
                    "dataType": "json", // do not supply JSON headers
                },
            },

            "plugins" : ["contextmenu", "types", "wholerow", "unique"],
            "contextmenu": {
                "items": function ($node) {
                    return {
                        "Rename": {
                            "label": "rename",
                            "action": function (data) {
                                var inst = $.jstree.reference(data.reference);
                                obj = inst.get_node(data.reference);
                                inst.edit(obj);
                            }
                        },
                        "Delete": {
                            "separator_before": true,
                            "label": "delete",
                            "action": function (data) {
                                var ref = $.jstree.reference(data.reference),
                                    sel = ref.get_selected();
                                if(!sel.length) { return false; }
                                ref.delete_node(sel);

                            }
                        },
                        "more info": {
                            "separator_before": true,
                            "label": "more info",
                            "action": function (data) {
                                var ref = $.jstree.reference(data.reference);
                                obj = ref.get_node(data.reference);

                                var type = '';
                                if (obj.data.indexOf('product') !== -1) {
                                    type = 'product';
                                    id = obj.data.replace(/[^.\d]+/g,"");
                                } else {
                                    type = 'category';
                                    id = obj.data;
                                }

                                $.get("/?cost=true&type="+type+"&id="+id, function(data) {
                                    obj = jQuery.parseJSON(data);

                                    var msg = 'Price '+ obj.price+"\r\n";
                                    if (obj.color !== undefined && obj.color !== '') {
                                        msg += 'Color '+ obj.color+"\r\n";
                                    }
                                    if (obj.url !== undefined && obj.url !== '') {
                                        msg += 'URL '+ obj.url+"\r\n";
                                    }
                                    alert(msg);
                                });
                            }
                        },
                    };
                }
            }
        });
    });
});

// helpers
function isURL(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
        '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
    return pattern.test(str);
}

function isFloat(n){
    var er = /^[0-9].[0-9]{2}$/;
    return er.test(n);
}