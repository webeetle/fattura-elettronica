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
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiVeicoli;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\DatiAnagrafici;
use Webeetle\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\Sede;
use Webeetle\FatturaElettronica\FatturaElettronicaFactory;
use Webeetle\FatturaElettronica\XmlValidator;
use PHPUnit\Framework\TestCase;

class FatturaSempliceConvenzioneTest extends TestCase {
  /**
   * @return DatiAnagrafici
   */
  public function testCreateAnagraficaCedente() {
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
  public function testCreateSedeCedente() {
    $sedeCedente = new Sede('IT', 'Via Roma 10', '33018', 'Tarvisio', 'UD');
    $this->assertInstanceOf(Sede::class, $sedeCedente);
    return $sedeCedente;
  }

  /**
   * @depends testCreateAnagraficaCedente
   * @depends testCreateSedeCedente
   * @param DatiAnagrafici $datiAnagrafici
   * @param Sede $sede
   * @return FatturaElettronicaFactory
   */
  public function testCreateFatturaElettronicaFactory(DatiAnagrafici $datiAnagrafici, Sede $sede) {
    $feFactory = new FatturaElettronicaFactory(
      $datiAnagrafici,
      $sede,
      '+39123456789',
      'info@deved.it'
    );
    $this->assertInstanceOf(FatturaElettronicaFactory::class, $feFactory);
    return $feFactory;
  }

  /**
   * @return DatiAnagrafici
   */
  public function testCreateAnagraficaCessionario() {
    $anaCessionario = new DatiAnagrafici('XYZYZX77M04H888K', 'Pinco Palla');
    $this->assertInstanceOf(DatiAnagrafici::class, $anaCessionario);
    return $anaCessionario;
  }

  /**
   * @return Sede
   */
  public function testCreateSedeCessionario() {
    $sedeCessionario = new Sede('IT', 'Via Diaz 35', '33018', 'Tarvisio', 'UD');
    $this->assertInstanceOf(Sede::class, $sedeCessionario);
    return $sedeCessionario;
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
   * @return DatiGenerali\DatiDdt
   */
  public function testDatiDdt() {
    $datiDdt = new DatiGenerali\DatiDdt('A1', '2018-11-10', ['1', '2']);
    $datiDdt->addDatiDdt(new DatiGenerali\DatiDdt('A2', '2018-12-09', ['3', '4']));
    $this->assertInstanceOf(DatiGenerali\DatiDdt::class, $datiDdt);
    return $datiDdt;
  }

  /**
   * @return DatiGenerali\DatiDdt
   */
  public function testDatiConvenzione() {
    $datiConvenzione = new DatiGenerali\DatiConvenzione('A1', '2018-11-11', ['1', '2'], 'PO-00001');
    $this->assertInstanceOf(DatiGenerali\DatiConvenzione::class, $datiConvenzione);
    return $datiConvenzione;
  }

  /**
   * @return DatiGenerali\DatiSal
   */
  public function testDatiSal() {
    $datiDdt = new DatiGenerali\DatiSal(1);
    $this->assertInstanceOf(DatiGenerali\DatiSal::class, $datiDdt);
    return $datiDdt;
  }

  /**
   * @depends testDatiDdt
   * @depends testDatiSal
   * @depends testDatiConvenzione
   * @param DatiGenerali\DatiDdt $datiDdt
   * @return DatiGenerali
   */
  public function testCreateDatiGenerali(DatiGenerali\DatiDdt $datiDdt, DatiGenerali\DatiSal $datiSal, DatiGenerali\DatiConvenzione $datiConvenzione) {
    $datiGenerali = new DatiGenerali(
      TipoDocumento::Fattura,
      '2018-11-22',
      '2018221111',
      122
    );
    $datiGenerali->setDatiDdt($datiDdt);
    $datiGenerali->setDatiSal($datiSal);
    $datiGenerali->setDatiConvenzione($datiConvenzione);
    $datiGenerali->Causale = "Fattura di prova";
    $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);
    return $datiGenerali;
  }

  /**
   * @return DatiPagamento
   */
  public function testCreateDatiPagamento() {
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
  public function testCreateLinee() {
    $linee = [];
    $linee[] = new Linea('Articolo1', 50, 'ABC');
    $linee[] = new Linea('Articolo2', 25, '3286340685115', 2, 'pz', 22.00, 'EAN');
    $this->assertCount(2, $linee);
    return $linee;
  }

  /**
   * @param array $linee
   * @depends testCreateLinee
   * @return DettaglioLinee
   */
  public function testCreateDettaglioLinee($linee) {
    $dettaglioLinee = new DettaglioLinee($linee);
    $this->assertInstanceOf(DettaglioLinee::class, $dettaglioLinee);
    return $dettaglioLinee;
  }

  /**
   * @return DatiVeicoli
   */
  public function testCreateDatiVeicoli() {
    $datiVeicoli = new DatiVeicoli(date('Y-m-d'), '100 KM');
    $this->assertInstanceOf(DatiVeicoli::class, $datiVeicoli);
    return $datiVeicoli;
  }

  /**
   * @depends testSetCessionarioCommittente
   * @depends testCreateDatiGenerali
   * @depends testCreateDatiPagamento
   * @depends testCreateDettaglioLinee
   * @depends testCreateDatiVeicoli
   * @param FatturaElettronicaFactory $factory
   * @param DatiGenerali $datiGenerali
   * @param DatiPagamento $datiPagamento
   * @param DettaglioLinee $dettaglioLinee
   * @param DatiVeicoli $datiVeicoli
   * @return \Webeetle\FatturaElettronica\FatturaElettronica
   * @throws \Exception
   */
  public function testCreateFattura(
    FatturaElettronicaFactory $factory,
    DatiGenerali $datiGenerali,
    DatiPagamento $datiPagamento,
    DettaglioLinee $dettaglioLinee,
    DatiVeicoli $datiVeicoli
  ) {
    $fattura = $factory->create(
      $datiGenerali,
      $datiPagamento,
      $dettaglioLinee,
      '12345',
      null,
      null,
      $datiVeicoli
    );
    $this->assertInstanceOf(FatturaElettronica::class, $fattura);
    return $fattura;
  }

  /**
   * @depends testCreateFattura
   * @param FatturaElettronica $fattura
   */
  public function testGetNomeFattura(FatturaElettronica $fattura) {
    $name = $fattura->getFileName();
    $this->assertTrue(strlen($name) > 5);
  }

  /**
   * @depends testCreateFattura
   * @param FatturaElettronica $fattura
   * @throws \Exception
   */
  public function testXmlSchemaFattura(FatturaElettronica $fattura) {
    $this->assertTrue($fattura->verifica());
  }
}
