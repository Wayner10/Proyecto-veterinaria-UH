<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="reportes-container">
    <h2>Reportes</h2>
    <p>Consulta y descarga reportes del sistema.</p>

    <!-- Filtros -->
    <form method="get" class="filtros-form">
        <label for="rol">Filtrar por rol:</label>
        <select name="rol" id="rol">
            <option value="">Todos</option>
            <option value="1">Administrador</option>
            <option value="2">Veterinario</option>
            <option value="3">Recepcionista</option>
            <option value="4">Cliente</option>
        </select>

        <label for="estado">Estado:</label>
        <select name="estado" id="estado">
            <option value="">Todos</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button type="submit" class="btn-filtrar">Filtrar</button>
    </form>
    <!-- Gráficas -->
    <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 30px;">
        <div style="flex: 1;">
            <h3>Usuarios por Rol</h3>
            <canvas id="graficoRoles" height="250"></canvas>
        </div>
        <div style="flex: 1;">
            <h3>Estado de Usuarios</h3>
            <canvas id="graficoEstado" height="250"></canvas>
        </div>
    </div>


    <!-- Botones de exportación -->
    <div class="export-buttons">
        <button onclick="exportarCSV()">Exportar CSV</button>
        <button onclick="window.print()">Imprimir</button>
    </div>

    <!-- Tabla -->
    <table class="tabla-reportes">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios ?? [] as $u): ?>
                <tr>
                    <td><?= $u['id_usuario'] ?></td>
                    <td><?= $u['nombre'] ?></td>
                    <td><?= $u['correo_electronico'] ?></td>
                    <td>
                        <?php
                            $roles = [1 => 'Administrador', 2 => 'Veterinario', 3 => 'Recepcionista', 4 => 'Cliente'];
                            echo $roles[$u['id_rol']] ?? 'Desconocido';
                        ?>
                    </td>
                    <td><?= $u['estado'] ? 'Activo' : 'Inactivo' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- JS simple para exportar CSV -->
<script>
function exportarCSV() {
    let csv = 'ID,Nombre,Correo,Rol,Estado\n';
    const filas = document.querySelectorAll('table tr');
    for (let i = 1; i < filas.length; i++) {
        const celdas = filas[i].querySelectorAll('td');
        const fila = Array.from(celdas).map(td => `"${td.innerText}"`).join(',');
        csv += fila + '\n';
    }

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'reporte_usuarios.csv';
    a.click();
}
</script>

<!-- Estilos -->
<style>
.reportes-container {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.filtros-form {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    align-items: center;
}
.filtros-form label {
    font-weight: 600;
}
.filtros-form select, .btn-filtrar {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.btn-filtrar {
    background: #2563eb;
    color: white;
    border: none;
    cursor: pointer;
}
.export-buttons {
    margin-bottom: 15px;
}
.export-buttons button {
    padding: 8px 15px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    margin-right: 10px;
    cursor: pointer;
}
.tabla-reportes {
    width: 100%;
    border-collapse: collapse;
}
.tabla-reportes th, .tabla-reportes td {
    border: 1px solid #e5e7eb;
    padding: 8px;
    text-align: left;
}
.tabla-reportes th {
    background: #f3f4f6;
}
</style>

<!-- Script de exportar CSV -->
<script>
function exportarCSV() {
    let csv = 'ID,Nombre,Correo,Rol,Estado\n';
    const filas = document.querySelectorAll('table tr');
    for (let i = 1; i < filas.length; i++) {
        const celdas = filas[i].querySelectorAll('td');
        const fila = Array.from(celdas).map(td => `"${td.innerText}"`).join(',');
        csv += fila + '\n';
    }

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'reporte_usuarios.csv';
    a.click();
}
</script>

<!-- Librería Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Tus gráficos -->
<script>
    // Datos desde PHP para los gráficos
    const rolesData = <?= json_encode(array_values($rolesCount)) ?>;
    const estadoData = <?= json_encode(array_values($estadoCount)) ?>;

    // Gráfico de barras: Usuarios por Rol
    const ctxRoles = document.getElementById('graficoRoles').getContext('2d');
    new Chart(ctxRoles, {
        type: 'bar',
        data: {
            labels: ['Administrador', 'Veterinario', 'Recepcionista', 'Cliente'],
            datasets: [{
                label: 'Cantidad',
                data: rolesData,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Usuarios por Rol' }
            }
        }
    });

    // Gráfico circular: Usuarios activos vs inactivos
    const ctxEstado = document.getElementById('graficoEstado').getContext('2d');
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: ['Activos', 'Inactivos'],
            datasets: [{
                label: 'Usuarios',
                data: estadoData,
                backgroundColor: ['#22c55e', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Estado de los Usuarios' }
            }
        }
    });
</script>

<?= $this->endSection() ?>
