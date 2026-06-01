<?php
include_once 'sql_functions.php';

$recipe_name = $instructions = $type_of_dish = null;
$duration = $calories =$delete_recipe_id= $serving = null;
$recipe_id = null;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_name']) && isset($_POST['isEditMode']) && $_POST['isEditMode'] === "false") {
    // Alapadatok tisztítása
    $recipe_name = htmlspecialchars($_POST['recipe_name']);
    $instructions = isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : null;
   $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : null;
    $calories = isset($_POST['calories']) ? htmlspecialchars($_POST['calories']) : null;
    $serving = isset($_POST['serving']) ? htmlspecialchars($_POST['serving']) : null;
    $type_of_dish = isset($_POST['type_of_dish']) ? htmlspecialchars($_POST['type_of_dish']) : null;

    // Recept beszúrása
    $recipe_id = insert_recipes($recipe_name, $duration, $type_of_dish, $instructions, $calories, $serving);

    // Hozzávalók kezelése
    if (!empty($_POST['amount']) && !empty($_POST['ingredient_name']) && !empty($_POST['unit'])) {
        $count = count($_POST['amount']);
        for ($i = 0; $i < $count; $i++) {
            $amount = htmlspecialchars($_POST['amount'][$i]);
            $ingredient_name = htmlspecialchars($_POST['ingredient_name'][$i]);
            $unit = htmlspecialchars($_POST['unit'][$i]);

            insert_ingredient($recipe_id, $amount, $unit, $ingredient_name, false);
        }
    }

    // Átirányítás
    if (!headers_sent()) {
        header("Location: recept_index.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_recipe'])) {
    // Alapadatok tisztítása
    $delete_recipe_id = (int)htmlspecialchars($_POST['delete_recipe']);
    delete_recipe($delete_recipe_id);

    // Átirányítás
    if (!headers_sent()) {
        header("Location: recept_index.php");
        exit();
    }
    }

 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['isEditMode']) && $_POST['isEditMode'] == "true" && isset($_POST['updateId'])) {

    $recipe_id = (int) $_POST['updateId'];
    $recipe_name = htmlspecialchars($_POST['recipe_name']);
    $instructions = isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : null;
    $duration = isset($_POST['duration']) ? (int) $_POST['duration'] : null;
    $calories = isset($_POST['calories']) ? (int) $_POST['calories'] : null;
    $serving = isset($_POST['serving']) ? (int) $_POST['serving'] : null;
    $type_of_dish = isset($_POST['type_of_dish']) ? htmlspecialchars($_POST['type_of_dish']) : null;
    update_recipes($recipe_id, $recipe_name, $duration, $type_of_dish, $instructions, $calories, $serving);
    // Átirányítás
    if (!headers_sent()) {
        header("Location: recept_index.php");
        exit();
    }
}

// dátum és ételek feltöltése a naptárba
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_date'])) {
    $datum_of_dish = $_POST['selected_date'];

     delete_records_by_date($datum_of_dish);

    // Több calendar_input támogatása (pl. name="calendar_input[]")
    if (isset($_POST['calendar_input']) && is_array($_POST['calendar_input'])) {
        foreach ($_POST['calendar_input'] as $recipe_name_calendar) {
            if (!empty($recipe_name_calendar)) {
                insert_dates_and_recipenames($datum_of_dish, $recipe_name_calendar);
            }
        }
    }

    // Átirányítás
    if (!headers_sent()) {
        header("Location: recept_index.php");
        exit();
    }
}


if (isset($_GET['calendar'])) {
    header('Content-Type: application/json');
    $mysqli = connect();

    // Lekérdezés paraméter
   $calendar = trim($_GET['calendar'] ?? '');

    if (strlen($calendar) < 2) {
        echo json_encode([]); // túl rövid lekérdezés, nincs találat
        exit;
    }

    $sql2 = "
        (
            SELECT DISTINCT recipe_name AS name FROM recipes
            WHERE recipe_name LIKE CONCAT(?, '%')
        )
        UNION
        (
            SELECT DISTINCT recipe_name AS name FROM calendar
            WHERE recipe_name LIKE CONCAT(?, '%')
        )
        LIMIT 10
    ";

    $stmt = $mysqli->prepare($sql2);
    $stmt->bind_param("ss", $calendar, $calendar);  // ✅ két paraméter kell
    $stmt->execute();
    $result = $stmt->get_result();

    // Találatok gyűjtése tömbbe
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row['name'];  // ✅ mert 'AS name' van az SQL-ben
    }

    header('Content-Type: application/json');  // ✅ Fontos, hogy JSON-t adunk vissza
    echo json_encode($recipes);
    exit;
}


if (isset($_GET['ingnames'])) {
    header('Content-Type: application/json');
    $mysqli = connect();

    // Lekérdezés paraméter
    $ingnames = trim($_GET['ingnames'] ?? '');

    if (strlen($ingnames) < 1) {
        echo json_encode([]); // túl rövid lekérdezés, nincs találat
        exit;
    }

    // SQL előkészítése és végrehajtása
    $stmt = $mysqli->prepare("SELECT DISTINCT ingredient_name FROM ingredients
     WHERE ingredient_name LIKE CONCAT(?, '%') LIMIT 10");
    $stmt->bind_param("s", $ingnames);
    $stmt->execute();
    $result = $stmt->get_result();

    // Találatok gyűjtése tömbbe
    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row['ingredient_name'];
    }

    echo json_encode($recipes);
    exit;
}

if (isset($_GET['query'])) {
    header('Content-Type: application/json');
    $mysqli = connect();

    $search = trim($_GET['query'] ?? '');

    if (strlen($search) < 1) {
        echo json_encode([]);
        exit;
    }

    $sql = "
    (SELECT DISTINCT recipe_name AS name, 'recipe' AS type FROM recipes
     WHERE recipe_name LIKE CONCAT(?, '%'))
    UNION
    (SELECT DISTINCT ingredient_name AS name, 'ingredient' AS type FROM ingredients
     WHERE ingredient_name LIKE CONCAT(?, '%'))
    LIMIT 20
";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $search, $search);  // KÉT darab paraméter kell!
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row; // vagy pl. $row['name'] ha csak a nevet akarod
    }

    echo json_encode($results);
    exit;
}
