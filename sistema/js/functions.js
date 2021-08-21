$(document).ready(function(){
    $("input[data-type='telefono']").mask('(000) 000-0000');
    $("input[data-type='celular']").mask('+1 (000) 000-0000');
    $('[data-toggle="tooltip"]').tooltip();

    $("input[data-type='currency']").on({
        keyup: function() {
        formatCurrency($(this));
        },
        blur: function() { 
        formatCurrency($(this), "blur");
        }
    });

    $("a[data-type='localizar'").click (function() {
        var search = "https://www.google.com/maps/search/?api=1&query=";
        var howtofind = "https://www.google.com/maps/dir/?api=1&destination=";
        var localizar = search + encodeURIComponent($(this).find('small').html());
        window.open(localizar);
    });

    $.fn.imgView =  function(imgID,name,color) {
        var imgID = "#" + imgID;
        $('#imagepreview').attr('src', $(imgID).attr('src')); // here asign the image to the modal when the user click the enlarge link
        $('.modal-title').text(name);
        $('.modal-header').css('background', color);
        $('#imagemodal').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function
    }

    $('#clase').ready(function() {
        if($('#clase').val() == 4){
            $('#precio').val('');
            $('#precio').parent('.form-group').hide();
        } else {
            $('#precio').parent('.form-group').show();
        }
    });

    $('#clase').on('change', function() {
        if(this.value == 4){
            $('#precio').val('');
            $('#precio').parent('.form-group').hide();
        } else {
            $('#precio').parent('.form-group').show();
        }
    });

    $('#clase').ready(function() {
        if($('#clase').val() == 3){
            $('#cantidad').val('');
            $('#cantidad').parent('.form-group').hide();
        } else {
            $('#cantidad').parent('.form-group').show();
        }
    });

    $('#clase').on('change', function() {
        if(this.value == 3){
            $('#cantidad').val('');
            $('#cantidad').parent('.form-group').hide();
        } else {
            $('#cantidad').parent('.form-group').show();
        }
    });

    //close navbar when click outside
    $(document).click(function (event) {
        var clickover = $(event.target);
        var _opened = $("#myNavbar").hasClass("navbar-collapse collapse in");
        if (_opened  && !clickover.hasClass("navbar-toggle")) {
            $("button.navbar-toggle").click();
        }
    });
    //column checkbox select all or cancel
    $("#select_all").click(function () {
        
        var checked = this.checked;
        $(':checkbox').each(function (index,item) {
            item.checked = checked;
        });
    });

    //--------------------- SELECCIONAR FOTO PRODUCTO ---------------------
    $("#foto").on("change",function(){
    	var uploadFoto = document.getElementById("foto").value;
        var foto       = document.getElementById("foto").files;
        var nav = window.URL || window.webkitURL;
        var contactAlert = document.getElementById('form_alert');
        
            if(uploadFoto !='') {
                var type = foto[0].type;
                var name = foto[0].name;
                if(type != 'image/jpeg' && type != 'image/jpg' && type != 'image/png') {
                    contactAlert.innerHTML = '<p class="errorArchivo">El archivo no es válido.</p>';                        
                    $("#img").remove();
                    $(".delPhoto").addClass('notBlock');
                    $('#foto').val('');
                    return false;
                }else{  
                        contactAlert.innerHTML='';
                        $(".prevPhoto").css('background', 'transparent');
                        $("#img").remove();
                        $(".delPhoto").removeClass('notBlock');
                        var objeto_url = nav.createObjectURL(this.files[0]);
                        $(".prevPhoto").append("<img id='img' src="+objeto_url+">");
                        $(".upimg label").remove();
                        
                    }
              }else{
              	alert("No selecciono foto");
                $("#img").remove();
              }              
    });

    $('.delPhoto').click(function(){
    	$('#foto').val('');
    	$(".delPhoto").addClass('notBlock');
    	$("#img").remove();
        $(".prevPhoto").css('background', '');
        $("input[id='checkif'").removeAttr('value');

    });

    var fotoLOADED = $("input[type='file']").attr('value');
    if(fotoLOADED != 'inventario/') {
        $("#img").remove();
        $(".delPhoto").removeClass('notBlock');
        var objeto_url = fotoLOADED;
        $(".prevPhoto").append("<img id='img' src="+objeto_url+">");
        $(".prevPhoto").css('background', 'transparent');
        $(".upimg label").remove();
        $("input[id='checkif'").attr('value', fotoLOADED);
    }

    function clearIMG(){

    }

});

function formatNumber(n) {
  // format number 1000000 to 1,234,567
  return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}


function formatCurrency(input, blur) {
  // appends $ to value, validates decimal side
  // and puts cursor back in right position.
  
  // get input value
  var input_val = input.val();
  
  // don't validate empty input
  if (input_val === "") { return; }
  
  // original length
  var original_len = input_val.length;

  // initial caret position 
  var caret_pos = input.prop("selectionStart");
    
  // check for decimal
  if (input_val.indexOf(".") >= 0) {

    // get position of first decimal
    // this prevents multiple decimals from
    // being entered
    var decimal_pos = input_val.indexOf(".");

    // split number by decimal point
    var left_side = input_val.substring(0, decimal_pos);
    var right_side = input_val.substring(decimal_pos);

    // add commas to left side of number
    left_side = formatNumber(left_side);

    // validate right side
    right_side = formatNumber(right_side);
    
    // On blur make sure 2 numbers after decimal
    if (blur === "blur") {
      right_side += "00";
    }
    
    // Limit decimal to only 2 digits
    right_side = right_side.substring(0, 2);

    // join number by .
    input_val = "RD $" + left_side + "." + right_side;

  } else {
    // no decimal entered
    // add commas to number
    // remove all non-digits
    input_val = formatNumber(input_val);
    input_val = "RD $" + input_val;
    
    // final formatting
    if (blur === "blur") {
      input_val += ".00";
    }
  }
  
  // send updated string to input
  input.val(input_val);

  // put caret back in the right position
  var updated_len = input_val.length;
  caret_pos = updated_len - original_len + caret_pos;
  input[0].setSelectionRange(caret_pos, caret_pos);
}

function sortName(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("listaTabla");
  switching = true;
  // Set the sorting direction to ascending:
  dir = "asc";
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /* Check if the two rows should switch place,
      based on the direction, asc or desc: */
      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          // If so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          // If so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      // Each time a switch is done, increase this count by 1:
      switchcount ++;
    } else {
      /* If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again. */
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}

function sortNumber() {
  var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById("listaTabla");
  switching = true;
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[0];
      y = rows[i + 1].getElementsByTagName("TD")[0];
      //check if the two rows should switch place:
      if (Number(x.innerHTML) > Number(y.innerHTML)) {
        //if so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}