<?php

return [

    /*
     * Diferença máxima (em bandejas) tolerada entre o que saiu e a soma de
     * vendido + retornado antes de a conciliação ser marcada como divergente.
     */
    'tolerancia_conciliacao' => (int) env('GRANJA_TOLERANCIA_CONCILIACAO', 0),

];
