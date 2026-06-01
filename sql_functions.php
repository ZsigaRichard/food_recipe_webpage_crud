<?php
function connect(){
    $host = "localhost";      // vagy IP-cím, pl.: 127.0.0.1
    $user = "eletterd_recept"; // az adatbázis felhasználóneve
    $pass = "Nincsen3356";         // a felhasználó jelszava
    $db   = "eletterd_cookbook";   // az adatbázis neve

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Hibák dobása kivételként

    try {
        $mysqli = new mysqli($host, $user, $pass, $db);
        //$mysqli = new mysqli("localhost","root","","cookbook");
        //$mysqli->set_charset("utf8mb4");
        return $mysqli;
    } catch (mysqli_sql_exception $e) {
        // Hiba logolása, vagy fejlesztés alatt hibaüzenet kiírása
        error_log("Adatbázis hiba: " . $e->getMessage());
        echo "Hiba történt az adatbáziskapcsolat során.";
        exit;
 
}
}
function insert_recipes($name, $duration, $type_of_dish, $instructions, $calories, $serving) {
    // Kapcsolat (például)
    $mysqli = connect();

    $sql = "INSERT INTO recipes (recipe_name, duration, type_of_dish, instructions, calories, serving) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    // 'sissii' - string, int, string, string, int, int
    $stmt->bind_param("sissii", $name, $duration, $type_of_dish, $instructions, $calories, $serving);

    $stmt->execute();

    // Utolsó beszúrt ID lekérése
    $last_id = $mysqli->insert_id;

    $stmt->close();
    $mysqli->close();

    return $last_id;
}

function insert_ingredient($recipe_id, $amount, $unit, $ingredient_name, $optional = false) {
    $mysqli = connect();

    $sql = "INSERT INTO ingredients (recipe_id, amount, unit, ingredient_name, optional) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    // Bind paraméterek:
    // recipe_id - int
    // amount - float (double)
    // unit - string
    // ingredient_name - string
    // optional - boolean 
    $optional_int = $optional ? 1 : 0;

    $stmt->bind_param("idssi", $recipe_id, $amount, $unit, $ingredient_name, $optional_int);

    $stmt->execute();

    $stmt->close();
    $mysqli->close();
}
function display_recipes_with_ingredients($search = '') {
    $mysqli = connect();

    $search = $mysqli->real_escape_string(trim($search));

    // Ha van keresés, akkor csak a találatok jelenjenek meg
    if (!empty($search)) {
    $sql = "
    SELECT DISTINCT r.*
    FROM recipes r
    LEFT JOIN ingredients i ON r.recipe_id = i.recipe_id
    WHERE r.recipe_name LIKE CONCAT(?, '%')
       OR i.ingredient_name LIKE CONCAT(?, '%')
    ORDER BY r.recipe_id
";
    $stmt = $mysqli->prepare($sql);
    $like = "$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $resultRecipes = $stmt->get_result();
} else {
    $sql = "SELECT * FROM recipes ORDER BY recipe_id";
    $resultRecipes = $mysqli->query($sql);
}

    if (!$resultRecipes || $resultRecipes->num_rows === 0) {
        echo '<p class="d-flex justify-content-center align-items-center vh-100">Nincs találat a keresésre.</p>';
        return;
    }

    echo '<div class="accordion" id="recipesAccordion">';

    while ($recipe = $resultRecipes->fetch_assoc()) {
        $recipeId = $recipe['recipe_id'];
        $recipeName = htmlspecialchars($recipe['recipe_name']);
        $duration = (int)$recipe['duration'];
        $typeOfDish = htmlspecialchars($recipe['type_of_dish']);
        $instructions = nl2br(htmlspecialchars($recipe['instructions']));
        $calories = (int)$recipe['calories'];
        $serving = (int)$recipe['serving'];

        $ReceptId = "$recipeId";
        $collapseId = "collapse$recipeId";

        echo '
        <div id="' . $ReceptId . '" class="accordion-item" style="margin-left:5%; margin-right:5%;">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '">
<div id="recipeName-' . $ReceptId . '">' . $recipeName . '&nbsp;</div>(<div id="typeOfDish-' . $ReceptId . '">' . $typeOfDish . '</div>) - <div id="duration-' . $ReceptId . '">&nbsp;' . $duration . ' </div> &nbsp;perc
                </button>
            </h2>
            <div id="' . $collapseId . '" class="accordion-collapse collapse" aria-labelledby="' . $ReceptId . '" data-bs-parent="#recipesAccordion">
                <div class="accordion-body">
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal'.$recipeId.'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                </svg>
                Törlés
                </button>
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#myModal" onclick="modify_mode_update(); restore_input(' . $ReceptId . '); importIngredientsFromDOM(\'ingredient_ul-' . $ReceptId . '\', \'ing_parent\');ingredient_ajax_setup();">

                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                </svg>
                Szerkesztés</button>
                    <p><strong>Adag:</strong> <span id="serving-' . $ReceptId . '">' . $serving . ' fő</span></p>
                    <p><strong>Kalória:</strong> <span id="calories-' . $ReceptId . '">' . $calories . '</span> kcal/adag</p>
                    <h5>Hozzávalók:</h5>
                    <ul id="ingredient_ul-'.$ReceptId.'">';

        // Hozzávalók lekérdezése
        $sqlIngredients = "SELECT * FROM ingredients WHERE recipe_id = $recipeId";
        $resultIngredients = $mysqli->query($sqlIngredients);

        if ($resultIngredients && $resultIngredients->num_rows > 0) {
            while ($ingredient = $resultIngredients->fetch_assoc()) {
                $amount = $ingredient['amount'];
                $unit = htmlspecialchars(trim($ingredient['unit']));
                $name = htmlspecialchars($ingredient['ingredient_name']);
                $optional = $ingredient['optional'] ? " (opcionális)" : "";
                echo "<li><span class='amount'>$amount</span> <span class='unit'>$unit</span> <span class='name '>$name </span></li>";
            }
        } else {
            echo "<li>Nincs hozzávaló megadva.</li>";
        }

        echo "</ul>
                <p><strong>Elkészítés:</strong><br><div id='instructions-" . $ReceptId . "'>$instructions</div></p>
                </div>
            </div>
        </div>";
        echo '<div class="modal fade" id="confirmDeleteModal'.$recipeId.'" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">

      <div class="modal-header border-0 justify-content-center">
        <h5 class="modal-title w-100">Biztosan törlöd?</h5>
      </div>

      <div class="modal-body">
        <div class="d-flex justify-content-center gap-3">
          <form method="POST">
          <input type="hidden" name="delete_recipe" value="'.$recipeId.'">
          <button type="submit" class="btn btn-danger">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
  <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
</svg>
          Törlés</button>
          </form>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
        </div>
      </div>

    </div>
  </div>
</div>
';

    }

    echo '</div>'; // accordion vége

    $mysqli->close();
}
function delete_recipe($recipe_id){
    $mysqli = connect();

    if ($recipe_id && is_numeric($recipe_id)) {
        // Előkészített törlő lekérdezés
        $stmt = $mysqli->prepare("DELETE FROM recipes WHERE recipe_id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $recipe_id); // i = integer
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "A recept (ID: $recipe_id) sikeresen törölve lett.";
            } else {
                echo "Nem található ilyen recept, vagy már törölve lett.";
            }

            $stmt->close();
        } else {
            echo "Lekérdezés előkészítési hiba: " . $mysqli->error;
        }
    } else {
        echo "Érvénytelen recept ID.";
    }

    $mysqli->close();

    }
function update_recipes($id, $name, $duration, $type_of_dish, $instructions, $calories, $serving) {
    // Adatbázis kapcsolat
    $mysqli = connect();

    // SQL utasítás (UPDATE)
    $sql = "UPDATE recipes 
            SET recipe_name = ?, 
                duration = ?, 
                type_of_dish = ?, 
                instructions = ?, 
                calories = ?, 
                serving = ? 
            WHERE recipe_id = ?";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        die("Hiba az előkészített lekérdezésben: " . $mysqli->error);
    }

    $stmt->bind_param("sissiii", $name, $duration, $type_of_dish, $instructions, $calories, $serving, $id);

    $stmt->execute();

    $affected_rows = $stmt->affected_rows;

    $stmt->close();
    $mysqli->close();
    update_ingredients($id);
    return $affected_rows; // hány sort módosított
}

function update_ingredients($recipe_id) {
    $mysqli = connect();

    // Először töröljük az összes hozzávalót az adott recepthez
    $delete_sql = "DELETE FROM ingredients WHERE recipe_id = ?";
    $delete_stmt = $mysqli->prepare($delete_sql);
    $delete_stmt->bind_param("i", $recipe_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Most újra beillesztjük az új hozzávalókat, ha vannak
    if (!empty($_POST['amount']) && !empty($_POST['ingredient_name']) && !empty($_POST['unit'])) {
        $count = count($_POST['amount']);

        for ($i = 0; $i < $count; $i++) {
            // Védjük a bemenetet
            $amount = htmlspecialchars($_POST['amount'][$i]);
            $ingredient_name = htmlspecialchars($_POST['ingredient_name'][$i]);
            $unit = htmlspecialchars($_POST['unit'][$i]);

            insert_ingredient($recipe_id, $amount, $unit, $ingredient_name, false);
        }
    }

    $mysqli->close();
}


function insert_dates_and_recipenames($date_of_dish,$recipe_name_calendar) {
    // Kapcsolat (például)
    $mysqli = connect();

    $sql = "INSERT INTO calendar(date_of_dish,recipe_name) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql);

    // 'sissii' - string, int, string, string, int, int
    $stmt->bind_param("ss",$date_of_dish,$recipe_name_calendar);

    $stmt->execute();

    $last_id = $mysqli->insert_id;

    $stmt->close();
    $mysqli->close();

    return $last_id;
}
function render_monthly_recipe_calendar($year, $month) {
    $mysqli = connect();

    // Lekérdezzük az adott hónaphoz tartozó bejegyzéseket
    $start_date = "$year-$month-01";
    $end_date = date("Y-m-t", strtotime($start_date)); // hónap utolsó napja

    $sql = "SELECT date_of_dish, recipe_name FROM calendar 
            WHERE date_of_dish BETWEEN ? AND ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Több recept is lehet egy napon
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $date = $row['date_of_dish'];
        $name = $row['recipe_name'];

        if (!isset($recipes[$date])) {
            $recipes[$date] = [];
        }
        $recipes[$date][] = $name;
    }

    $stmt->close();
    $mysqli->close();

    // Hónap neve magyarul
    $month_names = [
        1 => "Január", 2 => "Február", 3 => "Március", 4 => "Április",
        5 => "Május", 6 => "Június", 7 => "Július", 8 => "Augusztus",
        9 => "Szeptember", 10 => "Október", 11 => "November", 12 => "December"
    ];

    $first_day = strtotime("$year-$month-01");
    $days_in_month = date("t", $first_day);
    $start_weekday = date("N", $first_day); // hétfő = 1, vasárnap = 7

    // Előző és következő hónap kiszámítása
    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }

    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        $next_month = 1;
        $next_year++;
    }

    // Nyilak HTML-je
    $prev_link = '<a href="?year=' . $prev_year . '&month=' . $prev_month . '" class="btn btn-outline-secondary btn-sm me-2">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left" viewBox="0 0 16 16">
<path d="M10 12.796V3.204L4.519 8zm-.659.753-5.48-4.796a1 1 0 0 1 0-1.506l5.48-4.796A1 1 0 0 1 11 3.204v9.592a1 1 0 0 1-1.659.753"/>
</svg>
</a>';

$next_link = '<a href="?year=' . $next_year . '&month=' . $next_month . '" class="btn btn-outline-secondary btn-sm ms-2">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-right" viewBox="0 0 16 16">
<path d="M6 12.796V3.204L11.481 8zm.659.753 5.48-4.796a1 1 0 0 0 0-1.506L6.66 2.451C6.011 1.885 5 2.345 5 3.204v9.592a1 1 0 0 0 1.659.753"/>
</svg>
</a>';

    // Kimenet HTML
    echo '<div id="calendar" class="container mt-4">';
    
    // Fejléc hónap neve nyilakkal
    echo '<div class="d-flex justify-content-center align-items-center mb-4">';
    echo $prev_link;
    echo '<h2 class="mb-0 mx-2">' . $month_names[intval($month)] . " $year" . '</h2>';
    echo $next_link;
    echo '</div>';

    echo '<table class="table table-bordered text-center">';
    echo '<thead class="table-dark"><tr>';
    echo '<th>H</th><th>K</th><th>Sz</th><th>Cs</th><th>P</th><th>Sz</th><th>V</th>';
    echo '</tr></thead><tbody><tr>';

    // Üres cellák a hónap elejéig
    for ($i = 1; $i < $start_weekday; $i++) {
        echo "<td></td>";
    }

    // Napok kiírása
for ($day = 1; $day <= $days_in_month; $day++) {
    $current_date = sprintf("%04d-%02d-%02d", $year, $month, $day);
    $current_date_foo = "$year-$month-$day";   

    echo "<td onclick='passCurrentDate(\"$current_date_foo\")' style='min-height:100px; vertical-align:top; cursor:pointer' data-bs-toggle='modal' data-bs-target='#calendar-input'>";

    echo "<strong>$day</strong><br>";

    if (isset($recipes[$current_date])) {
        foreach ($recipes[$current_date] as $recipe_name) {
    echo "<div data-date=\"$current_date_foo\" class='from-calendar-recipes text-success mt-1' style='font-size: 0.9rem;'><b>" . htmlspecialchars($recipe_name) . "</b></div>";
 
        }
    }
    echo "</td>";

    // Új sor minden vasárnap után
    if ((($day + $start_weekday - 1) % 7) == 0) {
        echo "</tr><tr>";
    }
}


    // Üres cellák a hónap végén
    $end_day_of_week = ($days_in_month + $start_weekday - 1) % 7;
    if ($end_day_of_week != 0) {
        for ($i = $end_day_of_week + 1; $i <= 7; $i++) {
            echo "<td></td>";
        }
    }
     echo "</div>";
    echo '</tr></tbody></table></div>';
}

function delete_records_by_date($date) {
    // Kapcsolat létrehozása
    $conn = connect();
    if (!$conn) {
        die("Adatbázis kapcsolat sikertelen.");
    }

    // Előkészített utasítás a biztonságos törléshez
    $stmt = $conn->prepare("DELETE FROM calendar WHERE date_of_dish = ?");
    if (!$stmt) {
        die("Előkészítés sikertelen: " . $conn->error);
    }

    // Paraméter kötése
    $stmt->bind_param("s", $date);

    // Végrehajtás
    if ($stmt->execute()) {
        echo "Sikeresen törölve minden rekord a(z) $date dátummal.";
    } else {
        echo "Hiba a törlés során: " . $stmt->error;
    }

    // Takarítás
    $stmt->close();
    $conn->close();
}


?>