<?php
namespace App\Serializer;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use EasyRdf\Literal\Boolean;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AddIdFieldNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ID_NORMALIZER_ALREADY_CALLED';


    private $decorated;
    public function __construct(ResourceMetadataCollectionFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    private function addId($item){
        if(array_key_exists("@id", $item)){
            $tmp = explode("/",$item["@id"]);
            $item["id"] = end($tmp);
            if(intval($item["id"]) == $item["id"]){
                $item["id"] = intval($item["id"]);
            }    
        }
        return $item;
    }
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);
        
        if(array_key_exists("operation",$context)){
            $operation = $context["operation"];
            if($operation instanceof GetCollection){
                if($format === "jsonld" && array_key_exists("hydra:member", $data)){
                    $data["hydra:member"] = array_map(fn($item) => $this->addId($item), $data["hydra:member"] );
                }
            }else{
                if($format === "jsonld" ){
                    $data = $this->addId($data);
                }
            }
        }
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }
        return true;
    }

}
