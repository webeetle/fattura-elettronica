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

namespace Webeetle\FatturaElettronica\Tests;

use Webeetle\FatturaElettronica\Codifiche\ModalitaPagamento;
use Webeetle\FatturaElettronica\Codifiche\RegimeFiscale;
use Webeetle\FatturaElettronica\Codifiche\TipoDocumento;
use Webeetle\FatturaElettronica\FatturaElettronica;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\DettaglioLinee;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\Linea;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiGenerali;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiPagamento;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\DatiAnagrafici;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\Sede;
use Webeetle\FatturaElettronica\FatturaElettronicaFactory;
use Webeetle\FatturaElettronica\XmlValidator;
use PHPUnit\Framework\TestCase;

class FatturaIntermediarioTest extends TestCase
{
    /**
     * @return DatiAnagrafici
     */
    public function testCreateAnagraficaCedente()
    {
        $anagraficaCedente = new DatiAnagrafici(
            '12345678901',
            'Acme SpA',
            'IT',
            '12345678901',
            RegimeFiscale::Ordinario
        );
        $this->assertInstanceOf(DatiAnagrafici::class, $anagraficaCedente);
        return $anagraficaCedente;
    }

    /**
     * @return Sede
     */
    public function testCreateSedeCedente()
    {
        $sedeCedente = new Sede('IT', 'Via Roma 10', '33018', 'Tarvisio', 'UD');
        $this->assertInstanceOf(Sede::class, $sedeCedente);
        return $sedeCedente;
    }

    /**
     * @return DatiGenerali\DatiContratto
     */
    public function testCreateDatiContratto()
    {
        $datiContratto = new DatiGenerali\DatiContratto('123', [1, 2]);
        $datiContratto->Data = '2018-12-01';
        $datiContratto->CodiceCIG = 'ABCDEF';
        $datiContratto->addDatiContratto(new DatiGenerali\DatiContratto('234', [3,4]));
        $datiContratto->addDatiContratto(new DatiGenerali\DatiContratto('567'));
        $this->assertInstanceOf(DatiGenerali\DatiContratto::class, $datiContratto);
        return $datiContratto;
    }

    /**
     * @return FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea
     */
    public function testCreateIscrizioneRea()
    {
        $iscrizioneRea = new FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea(
            'UD',
            '286546'
        );
        $this->assertInstanceOf(FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea::class, $iscrizioneRea);
        return $iscrizioneRea;
    }

    /**
     * @depends testCreateAnagraficaCedente
     * @depends testCreateSedeCedente
     * @param DatiAnagrafici $datiAnagrafici
     * @param Sede $sede
     * @return FatturaElettronicaFactory
     */
    public function testCreateFatturaElettronicaFactory(DatiAnagrafici $datiAnagrafici, Sede $sede)
    {
        $feFactory = new FatturaElettronicaFactory(
            $datiAnagrafici,
            $sede,
            '+39123456789',
            'info@deved.it',
            new DatiAnagrafici('XYZYZX77M04H888K', 'Dati Cessionario')
        );
        $this->assertInstanceOf(FatturaElettronicaFactory::class, $feFactory);
        return $feFactory;
    }

    /**
     * @return DatiAnagrafici
     */
    public function testCreateAnagraficaCessionario()
    {
        $anaCessionario = new DatiAnagrafici('XYZYZX77M04H888K');
        $anaCessionario->Nome = 'Pinco';
        $anaCessionario->Cognome = 'Pallino';
        $this->assertInstanceOf(DatiAnagrafici::class, $anaCessionario);
        return $anaCessionario;
    }

    /**
     * @return Sede
     */
    public function testCreateSedeCessionario()
    {
        $sedeCessionario = new Sede('IT', 'Via Diaz 35', '33018', 'Tarvisio', 'UD');
        $this->assertInstanceOf(Sede::class, $sedeCessionario);
        return$sedeCessionario;
    }

    /**
     * @depends testCreateFatturaElettronicaFactory
     * @depends testCreateAnagraficaCessionario
     * @depends testCreateSedeCessionario
     * @param FatturaElettronicaFactory $factory
     * @param DatiAnagrafici $datiAnagrafici
     * @param Sede $sede
     * @return FatturaElettronicaFactory
     */
    public function testSetCessionarioCommittente(
        FatturaElettronicaFactory $factory,
        DatiAnagrafici $datiAnagrafici,
        Sede $sede
    ) {
        $factory->setCessionarioCommittente($datiAnagrafici, $sede);
        $this->assertInstanceOf(FatturaElettronicaFactory::class, $factory);
        return $factory;
    }

    /**
     * @depends testSetCessionarioCommittente
     * @depends testCreateIscrizioneRea
     * @param FatturaElettronicaFactory $factory
     * @param FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea $iscrizioneRea
     * @return FatturaElettronicaFactory
     */
    public function testSetIscrizioneRea(
        FatturaElettronicaFactory $factory,
        FatturaElettronica\FatturaElettronicaHeader\CedentePrestatore\IscrizioneRea $iscrizioneRea
    )
    {
        $factory->setIscrizioneRea($iscrizioneRea);
        $this->assertInstanceOf(FatturaElettronicaFactory::class, $factory);
        return $factory;
    }

    /**
     * @depends testCreateDatiContratto
     * @return DatiGenerali
     */
    public function testCreateDatiGenerali(DatiGenerali\DatiContratto $datiContratto)
    {
        $datiGenerali = new DatiGenerali(
            TipoDocumento::Fattura,
            '2018-11-22',
            '2018221111',
            122
        );
        $datiGenerali->setDatiContratto($datiContratto);
        $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);
        return $datiGenerali;
    }

    /**
     * @return DatiPagamento
     */
    public function testCreateDatiPagamento()
    {
        $datiPagamento = new DatiPagamento(
            ModalitaPagamento::SEPA_CORE,
            '2018-11-30',
            122
        );
        $this->assertInstanceOf(DatiPagamento::class, $datiPagamento);
        return $datiPagamento;
    }

    /**
     * @return array
     */
    public function testCreateLinee()
    {
        $linee = [];
        $linee[] = new Linea('Articolo1', 50, 'ABC');
        $linee[]= new Linea('Articolo2', 50, 'CDE');
        $linee[0]->DataInizioPeriodo = '2018-10-01';
        $linee[0]->DataFinePeriodo = '2018-10-31';
        $this->assertCount(2, $linee);
        return $linee;
    }

    /**
     * @param array $linee
     * @depends testCreateLinee
     * @return DettaglioLinee
     */
    public function testCreateDettaglioLinee($linee)
    {
        $dettaglioLinee = new DettaglioLinee($linee);
        $this->assertInstanceOf(DettaglioLinee::class, $dettaglioLinee);
        return $dettaglioLinee;
    }

    /**
     * @depends testSetIscrizioneRea
     * @depends testCreateDatiGenerali
     * @depends testCreateDatiPagamento
     * @depends testCreateDettaglioLinee
     * @param FatturaElettronicaFactory $factory
     * @param DatiGenerali $datiGenerali
     * @param DatiPagamento $datiPagamento
     * @param DettaglioLinee $dettaglioLinee
     * @return \Webeetle\FatturaElettronica\FatturaElettronica
     * @throws \Exception
     */
    public function testCreateFattura(
        FatturaElettronicaFactory $factory,
        DatiGenerali $datiGenerali,
        DatiPagamento $datiPagamento,
        DettaglioLinee $dettaglioLinee
    ) {
        $fattura = $factory->create($datiGenerali, $datiPagamento, $dettaglioLinee, '12345');
        $this->assertInstanceOf(FatturaElettronica::class, $fattura);
        return $fattura;
    }

    /**
     * @depends testCreateFattura
     * @param FatturaElettronica $fattura
     */
    public function testGetNomeFattura(FatturaElettronica $fattura)
    {
        $name = $fattura->getFileName();
        $this->assertTrue(strlen($name) > 5);
    }

    /**
     * @depends testCreateFattura
     * @param FatturaElettronica $fattura
     * @throws \Exception
     */
    public function testXmlSchemaFattura(FatturaElettronica $fattura)
    {
        //echo $fattura->toXml();
        $this->assertTrue($fattura->verifica());
    }
}
