<?php
require_once 'config.php';
require_once 'categories.php';

class YMLConverter {
    
    public function fetchYMLFeed($url) {
        error_log("=== FETCHING YML FEED FROM: {$url} ===");
        
        $response = $this->fetchWithCurl($url);
        if ($response === false) {
            error_log("cURL failed, trying file_get_contents");
            $response = $this->fetchWithFileGetContents($url);
        }
        
        if ($response === false) {
            error_log("All fetch methods failed");
            return null;
        }
        
        error_log("Response size: " . strlen($response) . " bytes");
        
        return $this->parseYML($response);
    }
    
    private function fetchWithCurl($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'YML-Converter/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/xml,text/xml,*/*',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log("cURL Error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            error_log("HTTP Error: {$httpCode}");
            return false;
        }
        
        return $response;
    }
    
    private function fetchWithFileGetContents($url) {
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['timeout' => 30]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            error_log("file_get_contents Error: " . ($error['message'] ?? 'Unknown'));
            return false;
        }
        
        return $response;
    }
    
    private function parseYML($xmlString) {
        // Очищаем строку
        $xmlString = trim($xmlString);
        
        if (empty($xmlString)) {
            error_log("Empty XML string");
            return null;
        }
        
        // Пробуем simplexml
        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if ($xml === false) {
            error_log("simplexml_load_string failed");
            
            // Пробуем удалить невалидные символы
            $xmlString = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $xmlString);
            $xmlString = preg_replace('/&(?!(?:amp|lt|gt|quot|apos);)/', '&amp;', $xmlString);
            
            $xml = @simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                error_log("XML Error: " . $error->message);
            }
            libxml_clear_errors();
            return null;
        }
        
        // Конвертируем в массив
        $json = json_encode($xml);
        if ($json === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            return null;
        }
        
        $array = json_decode($json, true);
        if ($array === null) {
            error_log("JSON decode error");
            return null;
        }
        
        return $array;
    }
    
    public function generateYoulaFeed($ymlData, $feedId) {
        error_log("=== GENERATING YOULA FEED ===");
        
        try {
            // Извлекаем данные магазина
            if (isset($ymlData['yml_catalog']['shop'])) {
                $shop = $ymlData['yml_catalog']['shop'];
            } elseif (isset($ymlData['shop'])) {
                $shop = $ymlData['shop'];
            } else {
                throw new Exception("Shop data not found in YML");
            }
            
            $shopName = $shop['name'] ?? 'Магазин';
            $shopUrl = $shop['url'] ?? '';
            
            error_log("Shop: {$shopName}");
            
            // Получаем товары
            $offers = [];
            if (isset($shop['offers']['offer'])) {
                $offers = $shop['offers']['offer'];
                if (!is_array($offers)) {
                    $offers = [$offers];
                }
            }
            
            error_log("Found offers: " . count($offers));
            
            // Создаем XML
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;
            
            $ymlCatalog = $dom->createElement('yml_catalog');
            $ymlCatalog->setAttribute('date', date('Y-m-d H:i'));
            $dom->appendChild($ymlCatalog);
            
            $shopElem = $dom->createElement('shop');
            $ymlCatalog->appendChild($shopElem);
            
            // Добавляем информацию о магазине
            $this->addElement($dom, $shopElem, 'name', $shopName);
            $this->addElement($dom, $shopElem, 'company', $shopName);
            $this->addElement($dom, $shopElem, 'url', $shopUrl);
            
            // Добавляем товары
            $offersElem = $dom->createElement('offers');
            $shopElem->appendChild($offersElem);
            
            $convertedCount = 0;
            foreach ($offers as $offer) {
                if ($convertedCount >= 1000) break; // Ограничиваем для теста
                
                $product = $this->convertProduct($offer, $shopUrl);
                if ($product) {
                    $this->addProductToXML($dom, $offersElem, $product);
                    $convertedCount++;
                }
            }
            
            error_log("Converted products: {$convertedCount}");
            
            // Сохраняем файл
            $feedPath = FEEDS_DIR . "/{$feedId}.xml";
            
            // Проверяем директорию
            if (!is_dir(FEEDS_DIR)) {
                mkdir(FEEDS_DIR, 0755, true);
            }
            
            // Пробуем разные методы сохранения
            $saved = $dom->save($feedPath);
            
            if ($saved === false) {
                // Альтернативный метод сохранения
                $xmlString = $dom->saveXML();
                if (file_put_contents($feedPath, $xmlString) === false) {
                    throw new Exception("Failed to save XML file");
                }
            }
            
            error_log("Feed saved to: {$feedPath}");
            error_log("File size: " . filesize($feedPath) . " bytes");
            
            return $feedPath;
            
        } catch (Exception $e) {
            error_log("EXCEPTION in generateYoulaFeed: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    private function convertProduct($ymlProduct, $shopUrl) {
        try {
            // Извлекаем ID
            if (is_array($ymlProduct) && isset($ymlProduct['@attributes']['id'])) {
                $productId = $ymlProduct['@attributes']['id'];
            } else {
                $productId = uniqid();
            }
            
            // Основные поля
            $name = $ymlProduct['name'] ?? '';
            $price = floatval($ymlProduct['price'] ?? 0);
            
            if (empty($name) || $price <= 0) {
                return null;
            }
            
            // Категория
            $categoryId = $ymlProduct['categoryId'] ?? '';
            $categoryName = $ymlProduct['category'] ?? '';
            $youlaCategory = CategoryMapper::mapCategory($categoryId, $categoryName);
            
            // Формируем продукт
            $product = [
                'id' => $productId,
                'available' => ($ymlProduct['available'] ?? 'true') == 'true',
                'url' => $ymlProduct['url'] ?? $shopUrl,
                'price' => $price,
                'currencyId' => $ymlProduct['currencyId'] ?? 'RUR',
                'categoryId' => $youlaCategory['categoryId'],
                'pictures' => [],
                'name' => $name,
                'description' => $ymlProduct['description'] ?? '',
                'vendor' => $ymlProduct['vendor'] ?? '',
                'model' => $ymlProduct['model'] ?? '',
                'params' => []
            ];
            
            // Добавляем параметр категории если есть
            if ($youlaCategory['param_name'] && $youlaCategory['param_value']) {
                $product['params'][] = [
                    'name' => $youlaCategory['param_name'],
                    'value' => $youlaCategory['param_value']
                ];
            }
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Error converting product: " . $e->getMessage());
            return null;
        }
    }
    
    private function addProductToXML($dom, $offersElem, $product) {
        $offer = $dom->createElement('offer');
        $offer->setAttribute('id', $product['id']);
        $offer->setAttribute('available', $product['available'] ? 'true' : 'false');
        
        $this->addElement($dom, $offer, 'url', $product['url']);
        $this->addElement($dom, $offer, 'price', $product['price']);
        $this->addElement($dom, $offer, 'currencyId', $product['currencyId']);
        $this->addElement($dom, $offer, 'categoryId', $product['categoryId']);
        
        if (!empty($product['description'])) {
            $this->addElement($dom, $offer, 'description', $product['description']);
        }
        
        $this->addElement($dom, $offer, 'name', $product['name']);
        
        if (!empty($product['vendor'])) {
            $this->addElement($dom, $offer, 'vendor', $product['vendor']);
        }
        
        if (!empty($product['model'])) {
            $this->addElement($dom, $offer, 'model', $product['model']);
        }
        
        // Параметры
        foreach ($product['params'] as $param) {
            $paramElem = $dom->createElement('param', htmlspecialchars($param['value']));
            $paramElem->setAttribute('name', $param['name']);
            $offer->appendChild($paramElem);
        }
        
        $offersElem->appendChild($offer);
    }
    
    private function addElement($dom, $parent, $name, $value) {
        if ($value !== null && $value !== '') {
            $element = $dom->createElement($name, htmlspecialchars($value));
            $parent->appendChild($element);
        }
    }
    
    public function countItems($ymlData) {
        if (isset($ymlData['yml_catalog']['shop']['offers']['offer'])) {
            $offers = $ymlData['yml_catalog']['shop']['offers']['offer'];
            return is_array($offers) ? count($offers) : 1;
        }
        return 0;
    }
}