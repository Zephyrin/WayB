<?php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Entity\User;
use App\Repository\MediaObjectRepository;
use App\Form\MediaObjectType;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use phpDocumentor\Reflection\Types\Mixed_;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Class MediaObjectController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/mediaobject",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="MediaObject"
 * )
 */
class MediaObjectController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var mediaObjectRepository
     */
    private $mediaObjectRepository;
    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        MediaObjectRepository $mediaObjectRepository,
        FormErrorSerializer $formErrorSerializer
    ) {
        $this->entityManager = $entityManager;
        $this->mediaObjectRepository = $mediaObjectRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Upload an image using MediaObject
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=200,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *      )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allow to create a link for an another user"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON MediaObject",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *     ),
     *     description="The JSon MediaObject"
     *    )
     *
     * )
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function postAction(Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(
            MediaObjectType::class,
            new MediaObject()
        );
        $form->submit(
            $data
        );
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                    'data' => $data
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $mediaObject = $form->getData();
        $this->manageImage($mediaObject, $data);
        $this->entityManager->persist($mediaObject);
        $this->entityManager->flush();
        return  $this->view(
            $mediaObject,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose the MediaObject.
     *
     * @SWG\Get(
     *     summary="Get the MediaObject based on its ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the MediaObject Entity based on ID",
     *     @SWG\Schema(ref=@Model(type=MediaObject::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The MediaObject based on ID does not exists"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the information about MediaObject"
     * )
     *
     *
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findMediaObjectById($id)
        );
    }

    /**
     * Expose all MediaObjects and their information
     *
     * @SWG\Get(
     *     summary="Get all MediaObjects",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all MediaObjects and their user information",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=MediaObject::class))
     *     )
     * )
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction(Request $request)
    {
        return $this->view(
            $this->mediaObjectRepository->findAll()
        );
    }

    /**
     * Update an MediaObject
     *
     * @SWG\Put(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The MediaObject based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON MediaObject",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *     ),
     *     description="The JSon MediaObject"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the MediaObject"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the MediaObject to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function putAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, true);
    }

    /**
     * Update a part of a MediaObject
     *
     * @SWG\Patch(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=204,
     *      description="Successful operation"
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Response(
     *     response=404,
     *     description="The MediaObject based on ID is not found"
     *    ),
     *    @SWG\Response(
     *     response=403,
     *     description="You are not allowed to update a MediaObject"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON MediaObject",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=MediaObject::class)
     *     ),
     *     description="The JSon MediaObject"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the MediaObject"
     *    )
     * )
     *
     * @param Request $request
     * @param string $id of the MediaObject to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function patchAction(Request $request, string $id)
    {
        return $this->putOrPatch($request, $id, false);
    }

    /**
     * Delete an MediaObject with the id.
     *
     * @SWG\Delete(
     *     summary="Delete an MediaObject based on ID"
     * )
     * @SWG\Response(
     *     response=204,
     *     description="The MediaObject is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The MediaObject based on ID is not found"
     * )

     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the MediaObject"
     * )
     * @param string $id
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, string $id)
    {
        $mediaObject = $this->findMediaObjectById($id);
        unlink($this->getParameter('media_object') . "/" . $mediaObject->getFilePath());
        $this->entityManager->remove($mediaObject);
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     *
     * @return MediaObject
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findMediaObjectById(string $id)
    {
        $existingMediaObject = $this->mediaObjectRepository->find($id);
        if (null === $existingMediaObject) {
            throw new NotFoundHttpException();
        }
        return $existingMediaObject;
    }

    private function putOrPatch(Request $request, string $id, bool $clearMissing)
    {
        $existingMediaObjectField = $this->findMediaObjectById($id);
        $form = $this->createForm(MediaObjectType::class, $existingMediaObjectField);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, $clearMissing);
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $mediaObject = $form->getData();
        $this->manageImage($mediaObject, $data);

        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    private function manageImage(MediaObject $mediaObject, $data)
    {
        if (!(isset($data['image']) || isset($data['img']))) { return; }
        $img64 = null; 
        if(isset($data['image'])) { $img64 = $data['image']; }
        if(isset($data['img'])) { $img64 = $data['img']; } 
        if ($img64) {
            if (preg_match('/^data:image\/(\w+)\+?\w*;base64,/', $img64, $type)) {
                $img64 = substr($img64, strpos($img64, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'svg'])) {
                    throw new \Exception('invalid image type');
                }

                $img = base64_decode($img64);

                if ($img === false) {
                    throw new \Exception('base64_decode failed');
                }
            } else {
                throw new \Exception('did not match data URI with image data');
            }
            $filename = $mediaObject->getFilePath();
            $oldfilename = null;
            if (!$filename) {
                $filename = uniqid() . "." . $type;
            } else if (!$this->endswith($filename, $type)){
                $oldfilename = $filename;
                $filename = uniqid() . "." . $type;
            }
            try {
                $directoryName = $this->getParameter('media_object');
                //Check if the directory already exists.
                if(!is_dir($directoryName)){
                    //Directory does not exist, so lets create it.
                    mkdir($directoryName, 0755);
                }
                error_log($directoryName);
                file_put_contents(
                    $directoryName . "/" . $filename,
                    $img
                );
            } catch (FileException $e) {
                throw new \Exception('cannot save image data to file');
            }

            $mediaObject->setFilePath($filename);
            if ($oldfilename) {
                unlink($this->getParameter('media_object') . "/" . $oldfilename);
            }
        }
    }

    private function endswith($string, $test) {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) return false;
        return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
    }
}
