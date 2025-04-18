<?php

require_once 'conexion.php';
require_once 'config/whatsapp.php';

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar variables
$mensaje_exito = '';
$mensaje_error = '';
$areas = [];
$abogada = [];
$estadisticas = [];

// Procesar formulario de consulta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_consulta'])) {
    try {
        $nombre = htmlspecialchars($_POST['nombre']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $telefono = htmlspecialchars($_POST['telefono']);
        $id_area = intval($_POST['id_area']);
        $descripcion = htmlspecialchars($_POST['descripcion']);
        
        // Obtener el nombre del área seleccionada
        $area_nombre = '';
        
        // Verificar que $areas sea un array antes de usarlo
        if (is_array($areas)) {
            foreach ($areas as $area) {
                if ($area['id_area'] == $id_area) {
                    $area_nombre = $area['nombre'];
                    break;
                }
            }
        } else {
            // Si $areas no es un array, usar un valor predeterminado
            $area_nombre = 'Área Legal';
        }
        
        // Insertar en la base de datos
        $stmt = $conn->prepare("INSERT INTO consultas (nombre, email, telefono, id_area, descripcion) 
                               VALUES (:nombre, :email, :telefono, :id_area, :descripcion)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':id_area' => $id_area,
            ':descripcion' => $descripcion
        ]);
        
        // Preparar datos para WhatsApp y correo electrónico
        $form_data = [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'area' => $area_nombre,
            'descripcion' => $descripcion
        ];
        
        // Generar enlace de WhatsApp
        $whatsapp_link = getWhatsAppConsultationLink($form_data);
        
        // Enviar correo electrónico
        $correo_enviado = enviarCorreoConsulta($form_data);
        
        if ($correo_enviado) {
            $mensaje_exito = "Su consulta ha sido enviada con éxito. Nos pondremos en contacto pronto.";
        } else {
            $mensaje_exito = "Su consulta ha sido enviada con éxito. Nos pondremos en contacto pronto.";
            $mensaje_error = "Hubo un problema al enviar el correo electrónico, pero su consulta ha sido registrada.";
        }
        
        $whatsapp_link_consulta = $whatsapp_link;
    } catch(PDOException $e) {
        $mensaje_error = "Error al enviar la consulta: " . $e->getMessage();
    }
}

// Obtener datos dinámicos
try {
    // Áreas de práctica específicas
    $sql_areas = "SELECT * FROM areas_practica 
                 WHERE nombre IN ('Sucesiones', 'Asesoría Mercantil', 'Penal', 'Inmobiliaria')
                 ORDER BY FIELD(nombre, 'Sucesiones', 'Asesoría Mercantil', 'Penal', 'Inmobiliaria')";
    $stmt_areas = $conn->query($sql_areas);
    $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
    
    // Información de la abogada
    $sql_abogada = "SELECT * FROM abogados WHERE nombre = 'Yumili Acuña' LIMIT 1";
    $stmt_abogada = $conn->query($sql_abogada);
    $abogada = $stmt_abogada->fetch(PDO::FETCH_ASSOC);
    
    // Estadísticas
    $sql_stats = "SELECT * FROM estadisticas";
    $stmt_stats = $conn->query($sql_stats);
    $estadisticas = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay resultados, usar datos de ejemplo
    if (empty($areas)) {
        $areas = [
            [
                'id_area' => 1,
                'nombre' => 'Sucesiones',
                'descripcion_corta' => 'Asesoramiento en procesos sucesorios y testamentos',
                'descripcion_larga' => 'Ofrecemos asesoramiento especializado en procesos sucesorios, testamentos, declaratorias de herederos y planificación patrimonial. Garantizamos un manejo eficiente y sensible de estos procesos familiares.',
                'icono' => 'fa-scroll',
                'imagen' => 'sucesiones-bg.jpg',
                'color' => '#8e44ad'
            ],
            [
                'id_area' => 2,
                'nombre' => 'Asesoría Mercantil',
                'descripcion_corta' => 'Soluciones legales para empresas y negocios',
                'descripcion_larga' => 'Brindamos asesoría integral en derecho mercantil: constitución de empresas, contratos comerciales, fusiones, adquisiciones y protección de marcas. Protegemos sus intereses comerciales.',
                'icono' => 'fa-briefcase',
                'imagen' => 'mercantil-bg.jpg',
                'color' => '#3498db'
            ],
            [
                'id_area' => 3,
                'nombre' => 'Penal',
                'descripcion_corta' => 'Defensa en procesos penales y delitos',
                'descripcion_larga' => 'Defensa especializada en procesos penales, delitos económicos, violencia doméstica y acusaciones criminales. Trabajamos para proteger sus derechos y garantizar un juicio justo.',
                'icono' => 'fa-gavel',
                'imagen' => 'penal-bg.jpg',
                'color' => '#e74c3c'
            ],
            [
                'id_area' => 4,
                'nombre' => 'Inmobiliaria',
                'descripcion_corta' => 'Asesoramiento en transacciones de propiedades',
                'descripcion_larga' => 'Soluciones legales para compraventa de propiedades, contratos de arrendamiento, propiedad horizontal y conflictos de linderos. Garantizamos la seguridad jurídica de sus transacciones inmobiliarias.',
                'icono' => 'fa-building',
                'imagen' => 'inmobiliaria-bg.jpg',
                'color' => '#2ecc71'
            ]
        ];
    }
    
    if (empty($abogada)) {
        $abogada = [
            'nombre' => 'Yumili Acuña',
            'titulo' => 'Abogada Especialista',
            'especialidad' => 'Derecho Civil y Comercial',
            'bio' => 'Abogada especializada con más de 10 años de experiencia representando clientes en casos complejos. Enfoque personalizado y compromiso con cada caso.',
            'telefono' => '+58 416 3117367',
            'email' => 'yumili@estudiojuridico.com',
            'whatsapp' => '4163117367',
            'foto' => 'yumili-acuna.jpg'
        ];
    }
    
    if (empty($estadisticas)) {
        $estadisticas = [
            ['titulo' => 'Años de Experiencia', 'valor' => '15+', 'icono' => 'fa-calendar-alt'],
            ['titulo' => 'Clientes Satisfechos', 'valor' => '1200+', 'icono' => 'fa-user-tie'],
            ['titulo' => 'Casos Exitosos', 'valor' => '95%', 'icono' => 'fa-check-circle'],
            ['titulo' => 'Asesoría Disponible', 'valor' => '24/7', 'icono' => 'fa-clock']
        ];
    }
} catch(PDOException $e) {
    error_log("Error al obtener datos: " . $e->getMessage());
    // Usar datos de ejemplo si hay error
    $areas = [
        [
            'id_area' => 1,
            'nombre' => 'Sucesiones',
            'descripcion_corta' => 'Asesoramiento en procesos sucesorios y testamentos',
            'descripcion_larga' => 'Ofrecemos asesoramiento especializado en procesos sucesorios, testamentos, declaratorias de herederos y planificación patrimonial. Garantizamos un manejo eficiente y sensible de estos procesos familiares.',
            'icono' => 'fa-scroll',
            'imagen' => 'sucesiones-bg.jpg',
            'color' => '#8e44ad'
        ],
        [
            'id_area' => 2,
            'nombre' => 'Asesoría Mercantil',
            'descripcion_corta' => 'Soluciones legales para empresas y negocios',
            'descripcion_larga' => 'Brindamos asesoría integral en derecho mercantil: constitución de empresas, contratos comerciales, fusiones, adquisiciones y protección de marcas. Protegemos sus intereses comerciales.',
            'icono' => 'fa-briefcase',
            'imagen' => 'mercantil-bg.jpg',
            'color' => '#3498db'
        ],
        [
            'id_area' => 3,
            'nombre' => 'Penal',
            'descripcion_corta' => 'Defensa en procesos penales y delitos',
            'descripcion_larga' => 'Defensa especializada en procesos penales, delitos económicos, violencia doméstica y acusaciones criminales. Trabajamos para proteger sus derechos y garantizar un juicio justo.',
            'icono' => 'fa-gavel',
            'imagen' => 'penal-bg.jpg',
            'color' => '#e74c3c'
        ],
        [
            'id_area' => 4,
            'nombre' => 'Inmobiliaria',
            'descripcion_corta' => 'Asesoramiento en transacciones de propiedades',
            'descripcion_larga' => 'Soluciones legales para compraventa de propiedades, contratos de arrendamiento, propiedad horizontal y conflictos de linderos. Garantizamos la seguridad jurídica de sus transacciones inmobiliarias.',
            'icono' => 'fa-building',
            'imagen' => 'inmobiliaria-bg.jpg',
            'color' => '#2ecc71'
        ]
    ];
    $abogada = [
        'nombre' => 'Yumili Acuña',
        'titulo' => 'Abogada Especialista',
        'especialidad' => 'Derecho Civil y Comercial',
        'bio' => 'Abogada especializada con más de 10 años de experiencia representando clientes en casos complejos. Enfoque personalizado y compromiso con cada caso.',
        'telefono' => '+58 416 3117367',
        'email' => 'yumili@estudiojuridico.com',
        'whatsapp' => '4163117367',
        'foto' => 'yumili-acuna.jpg'
    ];
    $estadisticas = [
        ['titulo' => 'Años de Experiencia', 'valor' => '15+', 'icono' => 'fa-calendar-alt'],
        ['titulo' => 'Clientes Satisfechos', 'valor' => '1200+', 'icono' => 'fa-user-tie'],
        ['titulo' => 'Casos Exitosos', 'valor' => '95%', 'icono' => 'fa-check-circle'],
        ['titulo' => 'Asesoría Disponible', 'valor' => '24/7', 'icono' => 'fa-clock']
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudio Jurídico Acuña | Asesoramiento Legal Profesional</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #0a2540;
            --secondary-color: #d4af37;
            --light-color: #f5f5f5;
            --dark-color: #333;
            --medium-gray: #666;
            --white: #fff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --whatsapp: #25D366;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--white);
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
            padding: 5px 0;
        }
        
        .nav-links a:hover {
            color: var(--secondary-color);
        }
        
        .nav-links a.active {
            color: var(--secondary-color);
        }
        
        .nav-links a.active::after,
        .nav-links a:hover::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--secondary-color);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(10, 37, 64, 0.85), rgba(10, 37, 64, 0.85)), 
                        url('img/ley-library.jpg') center/cover no-repeat;
            color: var(--white);
            height: 90vh;
            display: flex;
            align-items: center;
            text-align: center;
            margin-top: 70px;
            position: relative;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .hero h1 {
            font-size: 3.2rem;
            margin-bottom: 25px;
            line-height: 1.2;
            font-family: 'Playfair Display', serif;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        /* About Section */
        .about-section {
            padding: 100px 0;
            background-color: var(--white);
        }
        
        .about-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .about-image {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s;
        }
        
        .about-image:hover img {
            transform: scale(1.05);
        }
        
        .about-content h2 {
            font-size: 2.5rem;
            margin-bottom: 25px;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 15px;
        }
        
        .about-content h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background-color: var(--secondary-color);
        }
        
        .about-content p {
            margin-bottom: 20px;
            font-size: 1.1rem;
            color: var(--medium-gray);
        }
        
        /* Áreas de Práctica */
        .areas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .area-card {
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-top: 4px solid var(--secondary-color);
        }
        
        .area-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .area-icon {
            padding: 20px;
            text-align: center;
            font-size: 2.5rem;
            color: var(--secondary-color);
            background-color: rgba(212, 175, 55, 0.1);
        }
        
        .area-content {
            padding: 20px;
        }
        
        .area-content h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-family: 'Playfair Display', serif;
        }
        
        .area-content p {
            margin-bottom: 20px;
            color: var(--medium-gray);
        }
        
        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background-color: var(--white);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            text-align: center;
        }
        
        .stat-item {
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* Consultation Section */
        .consultation-section {
            padding: 100px 0;
            background: linear-gradient(rgba(10, 37, 64, 0.9), rgba(10, 37, 64, 0.9)), url('img/law-books.jpg') center/cover;
            color: var(--white);
            text-align: center;
        }
        
        .consultation-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .consultation-content h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        
        .consultation-content p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        /* Modal de Consulta */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-content {
            background-color: var(--white);
            border-radius: 8px;
            width: 100%;
            max-width: 600px;
            padding: 40px;
            position: relative;
            animation: modalFadeIn 0.4s;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.8rem;
            cursor: pointer;
            color: var(--medium-gray);
            transition: color 0.3s;
        }
        
        .close-modal:hover {
            color: var(--primary-color);
        }
        
        .modal h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
            font-size: 2rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 15px 35px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
            font-size: 1.1rem;
            border: 2px solid transparent;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: transparent;
            color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            background-color: transparent;
            color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 60px 0 30px;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 50px;
        }
        
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--white);
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .footer-logo i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .footer-about p {
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .footer-links h3, .footer-contact h3 {
            font-size: 1.3rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-links h3::after, .footer-contact h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--secondary-color);
        }
        
        .footer-links ul {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--white);
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .footer-links a:hover {
            opacity: 1;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .contact-icon {
            margin-right: 15px;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .about-container {
                grid-template-columns: 1fr;
            }
            
            .about-image {
                order: -1;
                max-width: 600px;
                margin: 0 auto;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 15px;
            }
            
            .logo {
                margin-bottom: 15px;
            }
            
            .nav-links {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero {
                height: auto;
                padding: 120px 0;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                padding: 30px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
        
        /* Estilos para WhatsApp */
        .whatsapp-float {
            position: fixed;
            width: 60px;
            height: 60px;
            bottom: 40px;
            right: 40px;
            background-color: var(--whatsapp);
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .whatsapp-float:hover {
            background-color: #128C7E;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        .whatsapp-icon {
            margin-top: 3px;
        }
        
        .whatsapp-link {
            color: var(--whatsapp);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .whatsapp-link:hover {
            color: #128C7E;
            text-decoration: underline;
        }
        
        .whatsapp-button {
            background-color: var(--whatsapp);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .whatsapp-button:hover {
            background-color: #128C7E;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
        }
        
        .whatsapp-button i {
            font-size: 1.2rem;
        }
        
        .contact-item a {
            color: var(--white);
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .contact-item a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo">
                <i class="fas fa-balance-scale"></i> Estudio Jurídico Acuña
            </a>
            <nav class="nav-links">
                <a href="index.php" class="active">Inicio</a>
                <a href="equipo.php">Nuestra Abogada</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-content">
            <h1>Expertos en Soluciones Legales Integrales</h1>
            <p>Protección jurídica especializada con más de <?php echo htmlspecialchars($estadisticas[0]['valor'] ?? '15+'); ?> años de experiencia defendiendo sus derechos</p>
            <button id="openConsultation" class="btn btn-primary">Consultar mi caso</button>
        </div>
    </section>

    <section class="about-section">
        <div class="container about-container">
            <div class="about-content">
                <h2>Nuestra Asesoría Legal</h2>
                <p>En Estudio Jurídico Acuña ofrecemos asesoramiento especializado en las siguientes áreas del derecho:</p>
                
                <div class="areas-grid">
                    <?php foreach ($areas as $area): ?>
                    <div class="area-card">
                        <div class="area-icon">
                            <i class="fas <?php echo htmlspecialchars($area['icono']); ?>"></i>
                        </div>
                        <div class="area-content">
                            <h3><?php echo htmlspecialchars($area['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($area['descripcion_corta'] ?? $area['descripcion']); ?></p>
                            <button onclick="setArea(<?php echo $area['id_area']; ?>)" class="btn btn-secondary">Consultar</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <p style="margin-top: 40px;">Fundado en <?php echo date('Y') - intval(str_replace('+', '', $estadisticas[0]['valor'] ?? '15')); ?>, hemos representado con éxito a más de <?php echo htmlspecialchars($estadisticas[1]['valor'] ?? '1200+'); ?> clientes en casos complejos, logrando resultados favorables en el <?php echo htmlspecialchars($estadisticas[2]['valor'] ?? '95%'); ?> de nuestros casos.</p>
                <a href="equipo.php" class="btn btn-secondary">Conozca a nuestra abogada</a>
            </div>
            <div class="about-image">
                <img src="img/<?php echo htmlspecialchars($abogada['foto'] ?? 'abogada-profesional.jpg'); ?>" alt="Abogada profesional en el Estudio Jurídico">
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <?php foreach ($estadisticas as $stat): ?>
                <div class="stat-item">
                    <div class="stat-number">
                        <i class="fas <?php echo htmlspecialchars($stat['icono']); ?>"></i> <?php echo htmlspecialchars($stat['valor']); ?>
                    </div>
                    <div class="stat-label"><?php echo htmlspecialchars($stat['titulo']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="consultation-section">
        <div class="container consultation-content">
            <h2>¿Necesita asesoría legal especializada?</h2>
            <p>Nuestro equipo está listo para analizar su caso y ofrecerle la mejor estrategia legal personalizada. Contáctenos hoy mismo para una consulta inicial sin compromiso.</p>
            <button id="openConsultation2" class="btn btn-primary">Consultar mi caso</button>
        </div>
    </section>

    <!-- Modal de Consulta -->
    <div id="consultationModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Describa su caso legal</h2>
            
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success">
                    <?php echo $mensaje_exito; ?>
                    <?php if (isset($whatsapp_link_consulta)): ?>
                        <div style="margin-top: 15px;">
                            <a href="<?php echo $whatsapp_link_consulta; ?>" target="_blank" class="whatsapp-button">
                                <i class="fab fa-whatsapp"></i> Continuar por WhatsApp
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($mensaje_error): ?>
                <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>
            
            <form id="consultaForm" method="POST" action="">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono (opcional)</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="id_area">Área de interés</label>
                    <select id="id_area" name="id_area" class="form-control" required>
                        <option value="">Seleccione un área</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?php echo $area['id_area']; ?>">
                                <?php echo htmlspecialchars($area['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Describa su situación</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" required placeholder="Describa su caso con el mayor detalle posible..."></textarea>
                </div>
                
                <button type="submit" name="enviar_consulta" class="btn btn-primary btn-block">Enviar consulta</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-container">
                <div class="footer-about">
                    <div class="footer-logo">
                        <i class="fas fa-balance-scale"></i> Estudio Jurídico Acuña
                    </div>
                    <p>Ofrecemos soluciones legales integrales con profesionalismo, ética y compromiso con nuestros clientes.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="equipo.php">Nuestra Abogada</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3>Contacto</h3>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <p>Domicilio procesal. La Hoyada, Edificio Oficentro Edad, Piso 05, Oficina 5-54, Diagonal Plaza Diego Ibarra al lado del Hotel Sideral</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <p><a href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $abogada['telefono'] ?? '541134567890')); ?>"><?php echo htmlspecialchars($abogada['telefono'] ?? '+54 11 1234-5678'); ?></a></p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <p><a href="mailto:yumili3369@yahoo.com.ve">yumili3369@yahoo.com.ve</a></p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <p>
                            <a href="<?php echo getWhatsAppLink(); ?>" target="_blank" class="whatsapp-link">
                                WhatsApp: <?php echo htmlspecialchars($abogada['telefono'] ?? '+58 416 3117367'); ?>
                            </a>
                        </p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <p>
                            <a href="https://wa.me/584163117367" target="_blank" class="btn btn-primary" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fab fa-whatsapp"></i> Contactar ahora
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Estudio Jurídico Acuña. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Botón flotante de WhatsApp -->
    <a href="<?php echo getWhatsAppLink(); ?>" class="whatsapp-float" target="_blank" title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp whatsapp-icon"></i>
    </a>

    <script>
        // Manejo del modal
        const modal = document.getElementById('consultationModal');
        const openBtns = document.querySelectorAll('#openConsultation, #openConsultation2');
        const closeBtn = document.querySelector('.close-modal');
        
        openBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
        
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Función para establecer el área seleccionada desde las tarjetas
        function setArea(idArea) {
            document.getElementById('id_area').value = idArea;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        // Validación del formulario
        document.getElementById('consultaForm').addEventListener('submit', function(e) {
            const descripcion = document.getElementById('descripcion').value.trim();
            if (descripcion.length < 30) {
                e.preventDefault();
                alert('Por favor describa su caso con más detalle (mínimo 30 caracteres)');
                document.getElementById('descripcion').focus();
            }
        });
        
        // Efectos para botones
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Resaltar enlace activo en navegación
        const currentPage = window.location.pathname.split('/').pop() || 'index.php';
        document.querySelectorAll('.nav-links a').forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>