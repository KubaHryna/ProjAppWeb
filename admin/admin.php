<?php
// Rozpoczynamy sesję
session_start();

// Dołączamy plik konfiguracyjny
include('cfg.php');

/**
 * Funkcja wyświetlająca formularz logowania.
 * Używana, gdy użytkownik nie jest zalogowany.
 */
function FormularzLogowania() {
    $form = '
    <div class="login-form">
        <h2>Logowanie do panelu administracyjnego</h2>
        <form method="post" action="admin.php">
            <div>
                <label>Login:</label>
                <input type="text" name="login" required>
            </div>
            <div>
                <label>Hasło:</label>
                <input type="password" name="pass" required>
            </div>
            <input type="submit" name="logowanie" value="Zaloguj">
        </form>
    </div>';
    
    return $form;
}

/**
 * Funkcja generująca listę dostępnych podstron.
 * Pobiera dane z bazy danych i wyświetla je w tabeli.
 */
function ListaPodstron() {
    global $link;
    
    // Zapytanie do bazy danych w celu pobrania listy podstron
    $query = "SELECT * FROM page_list";
    $result = $link->query($query);
    
    // Zaczynamy tworzenie tabeli
    $output = '<div class="admin-list">
        <h2>Lista podstron</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Tytuł</th>
                <th>Akcje</th>
            </tr>';
    
    // Wyświetlanie wyników zapytania w tabeli
    while($row = $result->fetch_assoc()) {
        $output .= '<tr>
            <td>'.$row['id'].'</td>
            <td>'.$row['page_title'].'</td>
            <td>
                <a href="admin.php?action=edytuj&id='.$row['id'].'">Edytuj</a>
                <a href="admin.php?action=usun&id='.$row['id'].'" onclick="return confirm(\'Czy na pewno chcesz usunąć?\')">Usuń</a>
            </td>
        </tr>';
    }
    
    $output .= '</table>
        <p><a href="admin.php?action=dodaj">Dodaj nową podstronę</a></p>
    </div>';
    
    return $output;
}

/**
 * Funkcja do edytowania podstrony.
 * Jeżeli podano ID, wczytuje dane z bazy danych i umożliwia edycję.
 */
function EdytujPodstrone($id = null) {
    global $link;
    
    $title = '';
    $content = '';
    $status = 1;
    
    // Jeśli przekazano ID, pobieramy dane z bazy
    if($id) {
        $query = "SELECT * FROM page_list WHERE id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($row = $result->fetch_assoc()) {
            $title = $row['page_title'];
            $content = $row['page_content'];
            $status = $row['status'];
        }
    }
    
    // Generowanie formularza edycji lub dodawania
    $form = '
    <div class="edit-form">
        <h2>'.($id ? 'Edytuj' : 'Dodaj').' podstronę</h2>
        <form method="post" action="admin.php">
            <input type="hidden" name="id" value="'.($id ?? '').'">
            <div>
                <label>Tytuł:</label>
                <input type="text" name="page_title" value="'.$title.'" required>
            </div>
            <div>
                <label>Treść:</label>
                <textarea name="page_content" rows="10" required>'.$content.'</textarea>
            </div>
            <div>
                <label>
                    <input type="checkbox" name="status" value="1" '.($status ? 'checked' : '').'>
                    Strona aktywna
                </label>
            </div>
            <input type="submit" name="'.($id ? 'edytuj' : 'dodaj').'" value="'.($id ? 'Zapisz zmiany' : 'Dodaj stronę').'">
        </form>
    </div>';
    
    return $form;
}

/**
 * Funkcja do dodawania nowej podstrony do bazy danych.
 * Zwraca komunikat o powodzeniu lub błędzie.
 */
function DodajNowaPodstrone() {
    global $link;

    if (isset($_POST['dodaj'])) {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
        $status = isset($_POST['status']) ? 1 : 0;

        // Zapytanie SQL do wstawienia danych
        $query = "INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param('ssi', $title, $content, $status);

        // Wykonanie zapytania i zwrócenie odpowiedniego komunikatu
        if ($stmt->execute()) {
            return "Dodano nową podstronę.";
        } else {
            return "Błąd podczas dodawania podstrony.";
        }
    }

    return EdytujPodstrone();
}

/**
 * Funkcja do usuwania podstrony.
 * Usuwa wybraną podstronę z bazy danych na podstawie ID.
 */
function UsunPodstrone($id) {
    global $link;
    
    // Zapytanie SQL do usunięcia podstrony
    $query = "DELETE FROM page_list WHERE id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param('i', $id);
    
    // Wykonanie zapytania i zwrócenie komunikatu
    if($stmt->execute()) {
        return "Podstrona została usunięta.";
    } else {
        return "Błąd podczas usuwania podstrony.";
    }
}

// Zmienna przechowująca komunikat dla użytkownika
$message = '';

// Obsługa logowania
if(isset($_POST['logowanie'])) {
    if($_POST['login'] === $login && $_POST['pass'] === $pass) {
        $_SESSION['logged_in'] = true; // Ustalamy sesję, jeśli login i hasło są poprawne
    } else {
        $message = "Błędny login lub hasło!"; // Komunikat w przypadku błędnych danych
    }
}

// Obsługa wylogowywania
if(isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy(); // Niszczenie sesji
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel administracyjny</title>
    <style>
        /* Style formularzy, tabel i innych elementów */
        .login-form, .admin-list, .edit-form { 
            max-width: 800px; 
            margin: 20px auto; 
            padding: 20px; 
        }
        .message { 
            color: red; 
            margin: 10px 0; 
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .edit-form textarea {
            width: 100%;
            margin: 10px 0;
        }
        .edit-form input[type="text"] {
            width: 100%;
            padding: 5px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php
    // Wyświetlanie komunikatu o błędzie logowania, jeśli istnieje
    if(!empty($message)) {
        echo '<div class="message">'.$message.'</div>';
    }

    // Sprawdzenie, czy użytkownik jest zalogowany
    if(!isset($_SESSION['logged_in'])) {
        echo FormularzLogowania(); // Jeśli nie jest zalogowany, wyświetlamy formularz logowania
    } else {
        echo '<div style="text-align: right;"><a href="admin.php?action=logout">Wyloguj</a></div>';
        
        // Sprawdzanie akcji do wykonania
        if(isset($_GET['action'])) {
            switch($_GET['action']) {
                case 'edytuj':
                    if(isset($_POST['edytuj'])) {
                        // Obsługa zapisu zmian w podstronie
                        $id = $_POST['id'];
                        $title = $_POST['page_title'];
                        $content = $_POST['page_content'];
                        $status = isset($_POST['status']) ? 1 : 0;
                        
                        $query = "UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ?";
                        $stmt = $link->prepare($query);
                        $stmt->bind_param('ssii', $title, $content, $status, $id);
                        
                        if($stmt->execute()) {
                            echo "Zmiany zostały zapisane.";
                        }
                    }
                    echo EdytujPodstrone($_GET['id']);
                    break;
                    
                case 'usun':
                    echo UsunPodstrone($_GET['id']);
                    echo ListaPodstron();
                    break;
                    
                case 'dodaj':
                    echo DodajNowaPodstrone();
                    break;
                    
                default:
                    echo ListaPodstron();
            }
        } else {
            echo ListaPodstron(); // Domyślna lista podstron
        }
    }
    ?>
</body>
</html>
