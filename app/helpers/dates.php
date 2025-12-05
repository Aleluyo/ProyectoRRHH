<?php

if (!function_exists('fecha_es')) {
    function fecha_es($fecha) {
        if (!$fecha) return '';
        $timestamp = strtotime($fecha);
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $dia = date('d', $timestamp);
        $mes = $meses[date('n', $timestamp) - 1];
        $anio = date('Y', $timestamp);
        return "$dia de $mes de $anio";
    }
}

if (!function_exists('mes_es')) {
    function mes_es($fecha) {
        if (!$fecha) return '';
        $timestamp = strtotime($fecha);
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return $meses[date('n', $timestamp) - 1];
    }
}
