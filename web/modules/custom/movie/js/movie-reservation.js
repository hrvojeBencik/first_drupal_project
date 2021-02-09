jQuery(document).ready(function ($) {
  let submit = $('#submit');
  let movieTitle = submit.data('movie-title');
  let movieGenre = submit.data('movie-genre');

  let movieData = [movieTitle, movieGenre];

  submit.click(function() {
    let movieDay = $('#day option:selected').val();
    let customerName = $('#customerName').val();
    movieData.push(movieDay, customerName);

    let jsonMovieData = JSON.stringify(movieData);

    $.ajax({
      type: "POST",
      url: "/movie-reservation",
      data: {movieData: jsonMovieData},
    });
  });
});
