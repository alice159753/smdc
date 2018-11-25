<?php
/**
 * AES128加解密类
 * @author dy
 *
 */

class Aes{
	
    //密钥
    private $_secrect_key;
      
    public function __construct()
    {
        $this->_secrect_key = 'MYgGnQE2jDFADSFFDSEWsdD';
    }
    
    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encrypt($str, $secrect_key = '')
    {
    	if (!empty($secrect_key)) {
    		$this->_secrect_key = $secrect_key;
    	}
        //AES, 128 ECB模式加密数据
        $screct_key = $this->_secrect_key;
        $str = trim($str);
        $str = $this->addPKCS7Padding($str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
        $encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
        
       	return bin2hex($encrypt_str);
    }
      
    /**
     * 解密方法
     * @param string $str
     * @return string
     */
    public function decrypt($str, $secrect_key = '')
    {
    	if (!empty($secrect_key)) {
    		$this->_secrect_key = $secrect_key;
    	}
    	
        //AES, 128 ECB模式加密数据
        $screct_key = $this->_secrect_key;
        $str = pack("H*", $str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
        $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
        $encrypt_str = $this->stripPKSC7Padding($encrypt_str);
        $encrypt_str = trim($encrypt_str);
        return $encrypt_str;
    }
      
    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        
        return $source;
    }
    
    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    function stripPKSC7Padding($source)
    {
        $source = trim($source);
        $char = substr($source, -1);
        $num = ord($char);

        return $source;
    }
}