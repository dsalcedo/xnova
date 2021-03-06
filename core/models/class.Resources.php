<?php

/**
 _  \_/ |\ | /¯¯\ \  / /\6
 ¯  /¯\ | \| \__/  \/ /--\Core.
 * @author: Copyright (C) 2015 by Brayan Narvaez (Prinick) developer of xNova Revolution and Xnova
 * @author web: http://www.prinick.com
 * Todos los derechos reservados para éste código y tódo el núcleo del sistema
 * class Resources: se encarga de manejar toda la información de recursos de los planetas, por tanto hereda todo lo generado por las minas
 * se encarga de hacer la constante actualización de recursos en el sistema cada vez que el usuario entra al juego
*/

require('core/models/class.Production.php');

class Resources extends Production {
    
    protected $id;
    protected $metal;
    protected $cristal;
    protected $tritio;
    protected $materia;
    protected $prod_resources;
    protected $niveles;
    
    public function __construct($id_planeta) {
        $this->id = $id_planeta;
        $db = new Connect();    
        
        $sql = $db->query("SELECT edificios.fuente_base, edificios.planta_energia, edificios.reactor_fusion, 
        edificios.mina_metal, edificios.mina_cristal, edificios.mina_tritio, 
        edificios.almacen_metal, edificios.almacen_cristal, edificios.almacen_tritio, 
        edificios.satelites, edificios.modulos, edificios.almacen_materia, edificios.distribuidor, edificios.nanobots,
        planetas.metal, planetas.cristal, planetas.tritio, 
        planetas.ultima_act, planetas.temp_promd FROM edificios, planetas WHERE edificios.id_planeta='$this->id' 
        AND planetas.id_planeta='$this->id' LIMIT 1;");
                
        $dat = $db->recorrer($sql);
        $this->niveles = array(
            'mina_metal' => $dat['mina_metal'], 
            'mina_cristal' => $dat['mina_cristal'],
            'mina_tritio' => $dat['mina_tritio'], 
            'reactor_fusion' => $dat['reactor_fusion'], 
            'planta_energia' => $dat['planta_energia'], 
            'distribuidor' => $dat['distribuidor'], 
            'satelites' => $dat['satelites'], 
            'modulos' => $dat['modulos'], 
            'almacen_metal' => $dat['almacen_metal'], 
            'almacen_cristal' => $dat['almacen_cristal'], 
            'almacen_tritio' => $dat['almacen_tritio'], 
            'almacen_materia' => $dat['almacen_materia'],
            'nanobots' => $dat['nanobots']
        );
        
        $this->metal = $dat['metal'];
        $this->cristal = $dat['cristal'];
        $this->tritio = $dat['tritio'];
        
        $tiempo = time();
        $time = $tiempo - $dat['ultima_act'];
        parent::__construct($time,$this->niveles,$dat['fuente_base'],$dat['temp_promd'],$this->tritio);
        
        $prod_metal = $this->metal >= $this->getMetalCapacity() ? 0 : $this->getMetalProd();
        $prod_cristal = $this->cristal >= $this->getCristalCapacity() ? 0 : $this->getCristalProd();
        $prod_tritio = $this->tritio >= $this->getTritioCapacity() ? 0 : $this->getTritioProd();        
        $this->prod_resources = array(
            'metal' => $prod_metal,
            'cristal' => $prod_cristal,
            'tritio' => $prod_tritio,
        );
                    
        //Actualizacion de recursos cada tano
        $update = $db->query("UPDATE planetas SET ultima_act='$tiempo', metal= metal + '$prod_metal', 
        cristal= cristal + '$prod_cristal', tritio= tritio + '$prod_tritio' WHERE id_planeta='$this->id'");  
            
        $db->liberar($sql,$update);     
        $db->close();       
        unset($prod_metal,$prod_cristal,$prod_tritio,$tiempo,$db);
    }    
    
    public function ProdResources() {
        return $this->prod_resources;
    }
    
    public function AlmacenMetal() {
        return $this->getMetalCapacity();
    }

    public function AlmacenCristal() {
        return $this->getCristalCapacity();
    }
    
    public function AlmacenTritio() {
        return $this->getTritioCapacity();
    }
    
    public function EnergiaTotal() {   
        return floor($this->ProdEnergia()['total']);    
    } 
    
    public function EnergiaConsumo() {   
        return floor($this->ProdEnergia()['consumo_energia']);      
    }
    
    public function EnergiaSobrante() {
        return floor($this->ProdEnergia()['energia_sobrante']);      
    }
    
    protected function GetLevels() {
        return $this->niveles;
    }
}

?>