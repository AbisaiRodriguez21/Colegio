<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use Config\Database;

class TestDB extends Controller
{
    public function index()
    {
        try {
            $db = Database::connect();

            $db->initialize();

            if ($db->connID) {
                echo "<h2 style='color:green;'>✅ Conexión exitosa a fnxcom_sjs2025 desde CodeIgniter</h2>";
            } else {
                echo "<h2 style='color:red;'>❌ No se pudo conectar.</h2><br>";
                echo "<pre>";
                print_r($db);
                echo "</pre>";
            }

        } catch (\Throwable $e) {
            echo "<h2 style='color:red;'>Error: " . $e->getMessage() . "</h2>";
        }
    }
}
