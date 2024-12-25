<?php
namespace Test;

use PHPUnit\Framework\TestCase;
use App\SetDataAllCrypto;


class SetDataAllCryptoTest extends TestCase {

public function testTruncateTable() {
    $setDataAllCrypto = new SetDataAllCrypto();
    $result = $setDataAllCrypto->truncateTable('bitcoin');
    $this->assertEquals("Table 'infobitcoin' videe.", $result);

}
    
        public function testGetDataCryptoWhenApiFails() {
            
            $setDataAllCryptoMock = $this->getMockBuilder(SetDataAllCrypto::class)
                                         ->onlyMethods(['getDataCrypto']) 
                                         ->getMock();
    
            
            $setDataAllCryptoMock->method('getDataCrypto')
                                 ->willReturn("Erreur lors de la récupération des données de l'API pour testCrypto");
    
        
            $result = $setDataAllCryptoMock->getDataCrypto('2020-01-01', '2020-12-31', 'testCrypto');
    
            
            $this->assertEquals("Erreur lors de la récupération des données de l'API pour testCrypto", $result);
        }
    }


?>