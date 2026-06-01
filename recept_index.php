<?php
	include_once 'sql_functions.php';
	include_once 'form_handler.php';
?>
<!doctype html>
<html lang="hu">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#d1d633ff" />
		<title>Receptek</title>
		<link rel="icon" type="image/x-icon" href="favicon.jfif">
		<!-- Bootstrap -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
		<link href="style.css?v8" rel="stylesheet" />
		 <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.1/lottie.min.js"></script>


	</head>
	<body class="bg-muted"  onload="clearInputContainer();search_ajax_setup();">
		<h1 class="text-center mb-5">Receptek</h1>
		<div id="animationContainer">
			<div id="animation"></div>
		</div>
		

		<script>
			// Lottie animáció betöltése
			var animation = lottie.loadAnimation({
			container: document.getElementById('animation'), // a div
			renderer: 'svg',   // SVG renderelés
			loop: true,        // végtelenített lejátszás
			autoplay: true,    // automatikus indítás
			path: 'animation.json' // ide írd a JSON animáció fájl elérési útját
			});
		</script>

		<?php
		$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
		$month = isset($_GET['month']) ? intval($_GET['month']) : date("m");
		render_monthly_recipe_calendar($year, $month);
		?>
		<div class="container mt-5 mb-5">
			<form class="mx-auto d-flex" role="search" style="width: 80%;" method="GET" id="searchForm">
				<div style="position: relative; flex-grow: 1;">  <!-- input konténer -->
					<input name="search" id="searchInput" class="form-control me-2" type="search" placeholder="Keresés..." aria-label="Keresés" autocomplete="off">
					<ul id="searchDropdown" class="dropdown-menu" style="position: absolute; top: 100%; left: 0; right: 0; max-height: 200px; overflow-y: auto; display: none; z-index: 1050;"></ul>
				</div>
				<button class="btn btn-dark" type="submit">Keresés</button>
			</form>


		</div>

		<!-- calendar input modal -->
			<div class="modal fade" id="calendar-input" tabindex="-1" aria-labelledby="calendar-inputLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form id="calendar-form" method="POST">
						<div class="modal-header">
							<h5 id="calendar_input_title" class="modal-title">Naptár</h5>
							<input id="hidden-date-input" type="hidden" data-current_date="" value="">
							<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
						</div>
						<div class="modal-body">
							<div id="meal_parent">

							</div>
						<button id="new_meal" type="button"  onclick="createCalendarInput('meal_parent');" class="btn btn-primary mb-3">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cup-hot" viewBox="0 0 16 16">
								<path fill-rule="evenodd" d="M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l.131-.59a3 3 0 0 0 1.3-5.854l.221-.99A.5.5 0 0 0 13.5 6zM13 12.5a2 2 0 0 1-.316-.025l.867-3.898A2.001 2.001 0 0 1 13 12.5M2.64 13.825 1.123 7h11.754l-1.517 6.825A1.5 1.5 0 0 1 9.896 15H4.104a1.5 1.5 0 0 1-1.464-1.175"/>
								<path d="m4.4.8-.003.004-.014.019a4 4 0 0 0-.204.31 2 2 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.6.6 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3 3 0 0 1-.202.388 5 5 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 3.6 4.2l.003-.004.014-.019a4 4 0 0 0 .204-.31 2 2 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.6.6 0 0 0-.09-.252A4 4 0 0 0 3.6 2.8l-.01-.012a5 5 0 0 1-.37-.543A1.53 1.53 0 0 1 3 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a6 6 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 4.4.8m3 0-.003.004-.014.019a4 4 0 0 0-.204.31 2 2 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.6.6 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3 3 0 0 1-.202.388 5 5 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 6.6 4.2l.003-.004.014-.019a4 4 0 0 0 .204-.31 2 2 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.6.6 0 0 0-.09-.252A4 4 0 0 0 6.6 2.8l-.01-.012a5 5 0 0 1-.37-.543A1.53 1.53 0 0 1 6 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a6 6 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 7.4.8m3 0-.003.004-.014.019a4 4 0 0 0-.204.31 2 2 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.6.6 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3 3 0 0 1-.202.388 5 5 0 0 1-.252.382l-.019.025-.005.008-.002.002A.5.5 0 0 1 9.6 4.2l.003-.004.014-.019a4 4 0 0 0 .204-.31 2 2 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.6.6 0 0 0-.09-.252A4 4 0 0 0 9.6 2.8l-.01-.012a5 5 0 0 1-.37-.543A1.53 1.53 0 0 1 9 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a6 6 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 10.4.8"/>
							</svg>	
									Új étel
						</button>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
							<button type="submit" class="btn btn-primary">Mentés</button>
						</div>
					</form>
				</div>
			</div>
			</div>

		<!-- calendar input modal -->
		<!-- Új recept gomb -->
		<button type="button"   class="btn btn-success my-5 float-end" style="margin-right:10%;" data-bs-toggle="modal" data-bs-target="#myModal">
  		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">
			<path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
			<path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
			<path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
		</svg>	
		Új recept
		</button>

		<?php
			$search = $_GET['search'] ?? '';
			display_recipes_with_ingredients($search);
		?>

		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">Új recept hozzáadása</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
					</div>
					<div class="modal-body p-1" id="input_container">
						<form id="input_form" method="POST">
							<input type="hidden" name="isEditMode" id="isEditMode" value="false">
							<input type="hidden" name="updateId" id="updateId" value="">
							<!-- Recept neve -->
							<div class="form-floating col-12 mb-3 mx-auto">
								<input id="recipe_name_input" name="recipe_name" type="text" class="form-control" placeholder="" required>
								<label for="recipe_name"  class="form-label ps-3" id="inputGroup-sizing-default">Recept neve</label>
							</div>

							<!-- 2x2 mezők -->
							<div class="container">
								<div class="row">
									<!-- Időtartam -->
								<div class="row mx-auto">
									<div class="form-floating col-12 mb-3 ">
										<input type="number" class="form-control" id="duration" name="duration" placeholder="">
										<label for="duration" class="form-label ps-3">Elkészítési idő (perc)</label>
									</div>
								</div>

								<!-- Kalória -->
								<div class="row mx-auto">
									<div class="form-floating col-12 mb-3">
										<input type="number" class="form-control" id="calories" name="calories" placeholder="">
										<label for="calories" class="form-label ps-3">Kalória (1 adag)</label>
									</div>
								</div>

								<!-- Típus -->
								<div class="row mx-auto">
									<div class="col-12 mb-3">
										<label for="type_of_dish" class="form-label ps-3"></label>
										<select class="form-select" id="type" name="type_of_dish">
											<option value="">Válassz típust</option>
											<option value="leves">Leves</option>
											<option value="főétel">Főétel</option>
											<option value="desszert">Desszert</option>
											<option value="snack">Saláta</option>
										</select>
									</div>
								</div>

								<!-- Adag -->
								<div class="row mx-auto">
									<div class="form-floating col-12 mb-3">
										<input name="serving" type="number" class="form-control" id="serving" placeholder="">
										<label for="serving" class="form-label ps-3">Hány főre?</label>
									</div>
								</div>


							<!-- Új hozzávaló gomb -->
							<button id="new_ingredient" type="button" onclick="createInput('ing_parent');ingredient_ajax_setup();" class="btn btn-primary mb-3 w-50 mx-auto">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-beaker" viewBox="0 0 16 16">
								<path d="M9.5 3a.5.5 0 0 0 0 1H13V3zm2 2a.5.5 0 0 0 0 1H13V5zm-2 2a.5.5 0 0 0 0 1H13V7zm2 2a.5.5 0 0 0 0 1H13V9zm-2 2a.5.5 0 0 0 0 1H13v-1zm2 2a.5.5 0 0 0 0 1H13v-1z"/>
								<path d="M.5 0a.5.5 0 0 0-.354.854l.122.12A2.5 2.5 0 0 1 1 2.744V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V2.743a2.5 2.5 0 0 1 .732-1.768l.122-.121A.5.5 0 0 0 15.5 0zM2 2.743A3.5 3.5 0 0 0 1.535 1h12.93A3.5 3.5 0 0 0 14 2.743V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
							</svg>	
							Új hozzávaló
							</button>
							<div id="ing_parent"></div>

							<!-- Recept leírás -->
							<div class="form-floating mt-3">
								<textarea name="instructions" class="form-control" placeholder="Írj valamit ide..." id="floatingTextarea" oninput="autoGrow(this)"></textarea>
								<label class="ps-3" for="floatingTextarea">Recept leírása</label>
							</div>

							<!-- Modal lábléc -->
							<div class="modal-footer">
								<!--
								<button type="button" class="btn btn-danger" onclick="document.getElementById('input_form').reset(); clearInputContainer();">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
								<path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
								</svg>	
								Törlés</button>
								-->
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
								<button type="submit" id="uploadbtn" name="upload" class="btn btn-primary">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-up" viewBox="0 0 16 16">
								<path fill-rule="evenodd" d="M8 10a.5.5 0 0 0 .5-.5V3.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 3.707V9.5a.5.5 0 0 0 .5.5m-7 2.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5"/>
								</svg>	
								Feltöltés</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>

	<?php include_once 'footer.html'; ?>
	<script src="js_functions.js"></script>	
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

</body>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const mealParent = document.getElementById('meal_parent');

  Sortable.create(mealParent, {
    animation: 300, // sima animáció sorrendezés közben
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    dragClass: 'sortable-drag',
  });
});
</script>
</html>
