<?php
/**
 * This file is part of deved/fattura-elettronica
 *
 * Copyright (c) Salvatore Guarino <sg@deved.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader;

use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\DatiAnagrafici;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\Sede;
use Webeetle\FatturaElettronica\Traits\MagicFieldsTrait;
use Webeetle\FatturaElettronica\XmlSerializableInterface;

class CedentePrestatore implements XmlSerializableInterface
{
    use MagicFieldsTrait;
    /** @var DatiAnagrafici */
    protected $datiAnagrafici;
    /** @var Sede */
    protected $sede;
    /** @var IscrizioneRea */
    protected $iscrizioneRea;


    /**
     * CedentePrestatore constructor.
     * @param DatiAnagrafici $datiAnagrafici
     * @param Sede $sede
     * @param IscrizioneRea $iscrizioneRea
     */
    public function __construct(
        DatiAnagrafici $datiAnagrafici,
        Sede $sede,
        IscrizioneRea $iscrizioneRea = null
    ) {
        $this->datiAnagrafici = $datiAnagrafici;
        $this->sede = $sede;
        $this->iscrizioneRea = $iscrizioneRea;
    }

    /**
     * @param IscrizioneRea $iscrizioneRea
     */
    public function setIscrizioneRea(IscrizioneRea $iscrizioneRea)
    {
        $this->iscrizioneRea = $iscrizioneRea;
    }

    /**
     * @param \XMLWriter $writer
     * @return \XMLWriter
     */
    public function toXmlBlock(\XMLWriter $writer)
    {
        $writer->startElement('CedentePrestatore');
            $this->datiAnagrafici->toXmlBlock($writer);
            $this->sede->toXmlBlock($writer);
            if ($this->iscrizioneRea) {
                $this->iscrizioneRea->toXmlBlock($writer);
            }
            $this->writeXmlFields($writer);
        $writer->endElement();
        return $writer;
    }
}
