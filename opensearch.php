<?php
/**
 * Azra Doğalgaz — OpenSearch Description (v1.12.25)
 * Erişim: https://azradogalgaz.com/opensearch.xml
 *
 * Tarayıcıların adres çubuğuna doğrudan site içi arama eklemesini sağlar.
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';

header('Content-Type: application/opensearchdescription+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');

$firma = (string)ayar('firma_unvan', 'Azra Doğalgaz');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName><?= htmlspecialchars($firma, ENT_XML1) ?></ShortName>
  <LongName><?= htmlspecialchars($firma, ENT_XML1) ?> — İzmir doğalgaz arama</LongName>
  <Description>Azra Doğalgaz site içi arama — hizmet, ürün, kampanya, bilgi bankası</Description>
  <InputEncoding>UTF-8</InputEncoding>
  <Image height="16" width="16" type="image/x-icon"><?= SITE_URL ?>/assets/img/favicon.ico</Image>
  <Image height="64" width="64" type="image/png"><?= SITE_URL ?>/assets/img/favicon-256.png</Image>
  <Url type="text/html" method="get" template="<?= SITE_URL ?>/?ara={searchTerms}"/>
  <Url type="application/opensearchdescription+xml" rel="self" template="<?= SITE_URL ?>/opensearch.xml"/>
  <moz:SearchForm><?= SITE_URL ?></moz:SearchForm>
  <Language>tr-TR</Language>
  <OutputEncoding>UTF-8</OutputEncoding>
  <Developer><?= htmlspecialchars($firma, ENT_XML1) ?></Developer>
  <AdultContent>false</AdultContent>
  <SyndicationRight>open</SyndicationRight>
</OpenSearchDescription>
