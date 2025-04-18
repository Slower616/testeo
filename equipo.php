<?php
require_once 'conexion.php';

// Obtener información de la abogada Yumili Acuña
try {
    $stmt = $conn->prepare("SELECT * FROM abogados WHERE nombre = 'Yumili Acuña' LIMIT 1");
    $stmt->execute();
    $abogada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$abogada) {
        // Crear datos de ejemplo si no existe en la base de datos
        $abogada = [
            'nombre' => 'Yumili Acuña',
            'titulo' => 'Abogada Especialista',
            'especialidad' => 'Derecho Inmobiliario y Civil',
            'bio' => 'Abogada especializada en derecho inmobiliario con más de 10 años de experiencia representando clientes en casos complejos. Egresada de la Universidad Nacional de Derecho con honores. Miembro activo del Colegio de Abogados y especialista en solución de conflictos inmobiliarios.',
            'telefono' => '584163117367',
            'email' => 'yumili3369@yahoo.com.ve',
            'whatsapp' => '584163117367',
            'foto' => 'yumili-acuna.jpg'
        ];
    }
} catch(PDOException $e) {
    die("Error al obtener información del abogado: " . $e->getMessage());
}

// Obtener estadísticas para la sección de logros
try {
    $sql_stats = "SELECT * FROM estadisticas";
    $stmt_stats = $conn->query($sql_stats);
    $estadisticas = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($estadisticas)) {
        $estadisticas = [
            ['titulo' => 'Años de Experiencia', 'valor' => '15+', 'icono' => 'fa-calendar-alt'],
            ['titulo' => 'Clientes Satisfechos', 'valor' => '1200+', 'icono' => 'fa-user-tie'],
            ['titulo' => 'Casos Exitosos', 'valor' => '95%', 'icono' => 'fa-check-circle'],
            ['titulo' => 'Asesoría Disponible', 'valor' => '24/7', 'icono' => 'fa-clock']
        ];
    }
} catch(PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $estadisticas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestra Abogada | Estudio Jurídico Acuña</title>
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
                        url('img/law-books.jpg') center/cover no-repeat;
            color: var(--white);
            height: 60vh;
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
        
        .specialty-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .specialty-item {
            background-color: var(--light-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .contact-methods {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .contact-method {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .contact-method:hover {
            color: var(--secondary-color);
        }
        
        .contact-icon {
            width: 40px;
            height: 40px;
            background-color: var(--light-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-color);
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
            background: linear-gradient(rgba(10, 37, 64, 0.9), rgba(10, 37, 64, 0.9)), url('img/law-books-2.jpg') center/cover;
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
            
            .stats-grid {
                grid-template-columns: 1fr;
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
            
            .contact-methods {
                flex-direction: column;
                gap: 15px;
            }
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
                <a href="index.php">Inicio</a>
                <a href="equipo.php" class="active">Nuestra Abogada</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-content">
            <h1>Nuestra Abogada Especialista</h1>
            <p>Conozca a la profesional que estará a cargo de su caso, brindándole asesoramiento personalizado y especializado</p>
        </div>
    </section>

    <section class="about-section">
        <div class="container about-container">
            <div class="about-image">
                <img src="img/<?php echo htmlspecialchars($abogada['foto'] ?: 'abogada-profesional.jpg'); ?>" alt="<?php echo htmlspecialchars($abogada['nombre']); ?>">
            </div>
            <div class="about-content">
                <h2><?php echo htmlspecialchars($abogada['nombre']); ?></h2>
                <p class="lawyer-title" style="color: var(--secondary-color); font-weight: 600; margin-bottom: 20px;"><?php echo htmlspecialchars($abogada['titulo']); ?></p>
                
                <p><?php echo htmlspecialchars($abogada['bio']); ?></p>
                
                <h3 style="margin: 25px 0 15px; color: var(--primary-color);">Especialidades:</h3>
                <div class="specialty-list">
                    <span class="specialty-item">Derecho Inmobiliario</span>
                    <span class="specialty-item">Contratos y Transacciones</span>
                    <span class="specialty-item">Sucesiones y Herencias</span>
                    <span class="specialty-item">Usucapión</span>
                    <span class="specialty-item">Litigios Civiles</span>
                </div>
                
                <h3 style="margin: 25px 0 15px; color: var(--primary-color);">Contacto Directo:</h3>
                <div class="contact-methods">
                    <a href="tel:+584163117367" class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span>+58 416 3117367</span>
                    </a>
                    <a href="mailto:yumili3369@yahoo.com.ve" class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span>yumili3369@yahoo.com.ve</span>
                    </a>
                    <a href="https://wa.me/584163117367" class="contact-method">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <span>WhatsApp</span>
                    </a>
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
            <p>La abogada Yumili Acuña está disponible para atender su caso personalmente, ofreciendo soluciones legales efectivas y representación profesional.</p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="contacto.php" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i> Agendar Consulta
                </a>
                <a href="https://wa.me/584163117367" class="btn btn-secondary">
                    <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                </a>
            </div>
        </div>
    </section>

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
                        <p>+58 416 3117367</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <p>yumili3369@yahoo.com.ve</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <p>
                            <a href="https://wa.me/584163117367" target="_blank" class="whatsapp-link">
                                WhatsApp: +58 416 3117367
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

    <script>
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