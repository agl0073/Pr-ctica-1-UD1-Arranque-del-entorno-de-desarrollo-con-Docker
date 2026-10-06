<?php
/**
 * =====================================================================
 *  inc/heroe.php · Reglas del juego y datos del héroe          [CE 2.c]
 * =====================================================================
 *  Autor: Alejandro Segundo Ganoza Leiro · Variante: C · Pícara
 *
 *  Este fichero contiene SOLO PHP. Según las recomendaciones PSR-12,
 *  se omite la etiqueta de cierre final para evitar enviar espacios o
 *  saltos de línea accidentales en las cabeceras HTTP de respuesta.
 */

declare(strict_types=1);

// --- Constantes con const (reglas generales y variante C) ------------
const CLASE_HEROE   = 'Pícara';
const XP_POR_NIVEL  = 400;
const VIDA_BASE     = 50;
const MULT_VIDA     = 2;
const BLOQUES_BARRA = 20;
const NOMBRE_RIVAL  = 'Sombra Gemela';

// Constante definida con define()
define('PODER_RIVAL', 236);

// --- Datos del héroe (estadísticas recibidas como texto entre comillas)
$nombreHeroe     = 'Vex <Sin Nombre>';
$apodo           = null;
$lema            = '';
$fuerzaTxt       = '11';
$destrezaTxt     = '18';
$inteligenciaTxt = '12';
$constitucionTxt = '12';
$experienciaTxt  = '1890';
$vidaActualTxt   = '96';
$oroTxt          = '2045.6';
$esVeterano      = true;
