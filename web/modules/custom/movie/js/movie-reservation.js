jQuery(document).ready(function ($) {
  const submit = $('#submit');
  const movieTitle = submit.data('movie-title');
  const movieGenre = submit.data('movie-genre');


  submit.click(function() {
    const movieDay = $('#day option:selected').val();
    const customerName = $('#customerName').val();

    const movieData = {'title': movieTitle, 'genre': movieGenre, 'day': movieDay, 'customerName': customerName};

    const jsonMovieData = JSON.stringify(movieData);

    $.ajax({
      type: "POST",
      url: "/movie-reservation",
      data: {movieData: jsonMovieData},
    });
  });
});
