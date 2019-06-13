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

namespace Webeetle\FatturaElettronica;


use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea;
use Webeetle\FatturaElettronica\IntermediarioInterface;

class FatturaAdapter implements FatturaElettronicaInterface
{
    /** @var FatturaInterface | IntermediarioInterface */
    protected $fattura;
    /** @var FatturaElettronicaHeader\CedentePrestatore */
    protected $cedentePrestatore;
    /** @var FatturaElettronicaHeader\CessionarioCommittente */
    protected $cessionarioCommittente;
    /** @var FatturaElettronica */
    public $fatturaElettronica;
    /** @var XmlFactory */
    protected $xmlFactory;
    /** @var FatturaElettronicaHeader */
    protected $fatturaElettronicaHeader;
    /** @var FatturaElettronicaBody */
    protected $fatturaElettronicaBody;


    /**
     * FatturaAdapter constructor.
     * @param FatturaInterface $fattura
     * @param XmlFactory|null $xmlFactory
     */
    public function __construct(FatturaInterface $fattura, XmlFactory $xmlFactory = null)
    {
        if ($xmlFactory) {
            $this->xmlFactory = $xmlFactory;
        } else {
            $this->xmlFactory = new XmlFactory();
        }
        $this->fattura = $fattura;
        $this->setCedentePrestatore();
        $this->setCessionarioCommittente();
        $this->createFatturaElettronica();
    }

    protected function createFatturaElettronica()
    {
        $terzoIntermediario = null;
        $soggettoEmittente = 'TZ';
        if (array_key_exists(IntermediarioInterface::class, class_implements($this->fattura))) {
            $terzoIntermediario = $this->fattura->getAnagraficaIntermediario();
            $soggettoEmittente = $this->fattura->getSoggettoEmittente();
        }
        $this->fatturaElettronicaHeader = new FatturaElettronicaHeader(
            $this->fattura->getDatiTrasmissione(),
            $this->cedentePrestatore,
            $this->cessionarioCommittente,
            $terzoIntermediario,
            $soggettoEmittente
        );
        $this->fatturaElettronicaBody = new FatturaElettronicaBody(
            $this->fattura->getDatiGenerali(),
            $this->fattura->getDatiBeniServizi(),
            $this->fattura->getDatiPagamento()
        );
        $this->fatturaElettronica = new FatturaElettronica(
            $this->fatturaElettronicaHeader,
            $this->fatturaElettronicaBody,
            $this->xmlFactory
        );
    }

    protected function setCedentePrestatore()
    {
        $this->cedentePrestatore = new FatturaElettronicaHeader\CedentePrestatore(
            $this->fattura->getAnagraficaCedente(),
            $this->fattura->getSedeCedente()
        );
    }
    protected function setCessionarioCommittente()
    {
        $this->cessionarioCommittente = new FatturaElettronicaHeader\CessionarioCommittente(
            $this->fattura->getAnagraficaCessionario(),
            $this->fattura->getSedeCessionario()
        );
    }

    /**
     * Restituisce l'XML della fattura elettronica
     * @return string
     * @throws \Exception
     */
    public function toXml()
    {
        return $this->fatturaElettronica->toXml();
    }

    /**
     * Restituisce il nome della fattura conforme all'SDI
     * @return string
     */
    public function getFileName()
    {
        return $this->fatturaElettronica->getFileName();
    }

    /**
     * @param IscrizioneRea $iscrizioneRea
     * @return mixed
     */
    public function setIscrizioneRea(IscrizioneRea $iscrizioneRea)
    {
        $this->fatturaElettronicaHeader->cedentePrestatore->setIscrizioneRea($iscrizioneRea);
    }

    /**
     * Verifica l'xml della fattura
     * @return bool
     * @throws \Exception
     */
    public function verifica()
    {
        return $this->fatturaElettronica->verifica();
    }
}
