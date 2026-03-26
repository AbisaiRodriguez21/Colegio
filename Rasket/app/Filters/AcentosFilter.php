<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AcentosFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (strpos($response->getHeaderLine('Content-Type'), 'text/html') !== false) {
            
            $body = $response->getBody();

            $entidades = [
                '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;', 
                '&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;', 
                '&Ntilde;', '&ntilde;', '&Uuml;', '&uuml;'
            ];
            $acentos = [
                'Á', 'É', 'Í', 'Ó', 'Ú', 
                'á', 'é', 'í', 'ó', 'ú', 
                'Ñ', 'ñ', 'Ü', 'ü'
            ];

            $body = str_replace($entidades, $acentos, $body);

            $response->setBody($body);
        }
    }
}