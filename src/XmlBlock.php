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

use Webeetle\FatturaElettronica\Traits\MagicFieldsTrait;

abstract class XmlBlock implements XmlSerializableInterface
{
    use MagicFieldsTrait;
}
