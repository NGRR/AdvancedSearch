/**
 * JavaScript para los dropdowns personalizados con búsqueda
 */

// Función para mostrar el dropdown
function showDropdown(uniqueId) {
    // Cerrar todos los dropdowns abiertos primero
    closeAllDropdowns();
    
    // Mostrar el dropdown seleccionado
    document.getElementById('dropdown_' + uniqueId).style.display = 'block';
    
    // Enfocar el campo de búsqueda
    document.getElementById('filterInput_' + uniqueId).focus();
    
    // Agregar evento para cerrar el dropdown al hacer clic fuera
    document.addEventListener('click', function(event) {
        var dropdown = document.getElementById('dropdown_' + uniqueId);
        var searchBox = document.getElementById('searchBox_' + uniqueId);
        
        if (event.target !== dropdown && event.target !== searchBox && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Función para cerrar todos los dropdowns
function closeAllDropdowns() {
    var dropdowns = document.getElementsByClassName('dropdown-content');
    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].style.display = 'none';
    }
}

// Función para filtrar opciones en el dropdown
function filterFunction(uniqueId) {
    var input, filter, div, items, i, txtValue;
    input = document.getElementById('filterInput_' + uniqueId);
    filter = input.value.toUpperCase();
    div = document.getElementById('dropdown_' + uniqueId);
    items = div.getElementsByClassName('dropdown-item');
    
    for (i = 0; i < items.length; i++) {
        txtValue = items[i].textContent || items[i].innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            items[i].style.display = '';
        } else {
            items[i].style.display = 'none';
        }
    }
}

// Función para seleccionar una opción en el dropdown de categorías
function selectOption(uniqueId, value, text) {
    // Actualizar el valor del campo de búsqueda
    document.getElementById('searchBox_' + uniqueId).value = text;
    
    // Marcar la opción seleccionada
    var radios = document.getElementsByName('category');
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].value === value) {
            radios[i].checked = true;
        } else {
            radios[i].checked = false;
        }
    }
    
    // Cerrar el dropdown
    document.getElementById('dropdown_' + uniqueId).style.display = 'none';
    
    // Si es un cambio de categoría, cargar las etiquetas relacionadas
    if (uniqueId.startsWith('category_')) {
        loadTagsForCategory(value);
    }
}

// Función para actualizar la selección de etiquetas
function updateTagsSelection(uniqueId) {
    var checkboxes = document.querySelectorAll('#dropdown_' + uniqueId + ' input[type="checkbox"]:checked');
    var selectedTags = [];
    
    for (var i = 0; i < checkboxes.length; i++) {
        var label = document.querySelector('label[for="' + checkboxes[i].id + '"]');
        selectedTags.push(label.textContent);
    }
    
    // Actualizar el texto del campo de búsqueda
    var searchBox = document.getElementById('searchBox_' + uniqueId);
    if (selectedTags.length > 0) {
        searchBox.value = selectedTags.join(', ');
    } else {
        searchBox.value = '';
        searchBox.placeholder = 'Seleccionar etiquetas';
    }
}

// Función para cargar etiquetas relacionadas con una categoría
function loadTagsForCategory(categoryId) {
    // Esta función se implementará con AJAX para obtener las etiquetas relacionadas
    // Por ahora, simplemente actualizamos la UI
    var tagsDropdown = document.querySelector('[id^="searchBox_tags_"]');
    if (tagsDropdown) {
        tagsDropdown.value = '';
        
        // Desmarcar todas las etiquetas
        var checkboxes = document.querySelectorAll('input[name="tags[]"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = false;
        }
        
        // Si se implementa AJAX, aquí se cargarían las etiquetas relacionadas
        // y se actualizaría el dropdown
    }
}

// Inicializar los dropdowns cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la selección de etiquetas
    var tagsDropdowns = document.querySelectorAll('[id^="searchBox_tags_"]');
    for (var i = 0; i < tagsDropdowns.length; i++) {
        var uniqueId = tagsDropdowns[i].id.replace('searchBox_', '');
        updateTagsSelection(uniqueId);
    }
});
