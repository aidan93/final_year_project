$(document).ready(function () {
   
});



//autocomple search form
// function searchAutoComplete() {
//   $.ajax({
//     url: "xml/" + dayOfTheWeek + ".xml",
//     dataType: "xml",
//     success: function( xmlResponse ) {
//       var data = $( "Programme", xmlResponse ).map(function() {
//         return {
//           value: $( "Title", this ).text()
//         };
//       }).get();

//       //empty array to store unique programmes
//       var distinct = [];

//       //remove duplicates from the 'data' array and store unique values in 'distinct' array
//       $.each(data, function(key, object) {
//         if($.inArray(object.value, distinct) === -1) {
//           distinct.push(object.value);
//         }
//       });

//       //when data is inputted to 'search' box, provide autocomplete suggestions
//       $( "#search" ).autocomplete({
//         source: distinct,
//         minLength: 0,
//         autoFocus: true,
//         open: function() { 
//           $('.ui-autocomplete.ui-menu').width(230);

//           //get current position of autocomplete menu
//           var position = $(".ui-autocomplete.ui-menu").position(), top = position.top;

//           //apply 10px to top positioning to display just below search box
//           $(".ui-autocomplete.ui-menu").css({top: top + 10 + "px" }); 
//         },
//         select: function( event, ui ) {

//           //get description of selected programme
//           var programme = $(xmlResponse).find('Programme>Title').filter(function() {
//               return $(this).text() == ui.item.value;
//           }).closest('Programme');
//           var programmeDes = $('Full_Description', programme.first()).text();

//           var matchedItems = 0;

//           $.each(programme, function(key, object) {
//             matchedItems++;
//           }); 

//           //print the selected programme with details of programme and number of matched items
//           log(ui.item.value, programmeDes, matchedItems);
//         }
//       });
//     }
//   });
// }