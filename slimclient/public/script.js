$(document).ready(function() {
  $("#formMovies").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:8082/slimClient/movies",
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8082/slimClient");
      }
    });
  });
  $("#editMovies").submit(function(event) {
    alert( "TODO: build submit handler.  See peopleForm submit handler for inspiration " );
	var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "PUT",
      url: "http://localhost:8082/slimClient/Movies/" + $(this).attr("data-id"),
      data: form.serialize(),
      success: function(data) {
window.location.replace("http://localhost:8082/slimClient/movies");
	}
  });


  $( ".deletebtn" ).click(function() {
	if (window.confirm("are you sure you want to delete?")) {
         $.ajax({
           type: "DELETE",
           url: "http://localhost:8082/slimClient/movies" + $(this).attr("data-id"),
           success: function(data) {
window.location.reload();
	 }
    });
 }
});
});
