<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\User;
use App\Form\BrandType;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Serializer\FormErrorSerializer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class BrandController
 * @package App\Controller
 *
 * @Rest\RouteResource(
 *     "api/Brand",
 *     pluralize=false
 * )
 * @SWG\Tag(
 *     name="Brand"
 * )
 * 
 */
class BrandController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    /**
     * @var FormErrorSerializer
     */
    private $formErrorSerializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        BrandRepository $brandRepository,
        FormErrorSerializer $formErrorSerializer
    ) {
        $this->entityManager = $entityManager;
        $this->brandRepository = $brandRepository;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * Create a new Brand if user is AMBASSADOR.
     *
     *
     * @SWG\Post(
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *      response=201,
     *      description="Successful operation with the new value insert",
     *      @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     )
     *    ),
     *    @SWG\Response(
     *     response=422,
     *     description="The form is not correct<BR/>
     * See the corresponding JSON error to see which field is not correct"
     *    ),
     *    @SWG\Parameter(
     *     name="The JSON Brand",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     ),
     *     description="The JSon Brand"
     *    )
     *
     * )
     * 
     * @IsGranted("ROLE_AMBASSADOR")
     * @param Request $request
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function postAction(Request $request)
    {
        $connectUser = $this->getUser();

        $data = json_decode(
            $request->getContent(),
            true
        );
        $form = $this->createForm(BrandType::class, new Brand());
        $form->submit(
            $data
        );
        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        
        $insertData = $form->getData();
        $insertData->setCreatedBy($connectUser);
        if (
            !$this->isGranted("ROLE_AMBASSADOR")
            || $insertData->getValidate() === null
        )
            $insertData->setValidate(false);

        $this->entityManager->persist($insertData);

        $this->entityManager->flush();
        return  $this->view(
            $insertData,
            Response::HTTP_CREATED
        );
    }

    /**
     * Expose the Brand with the id
     *
     * @SWG\Get(
     *     summary="Get the Brand based on ID",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return the Brand based on ID",
     *     @SWG\Schema(ref=@Model(type=Brand::class))
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The Brand based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     * )
     *
     * @var $id
     * @return \FOS\RestBundle\View\View
     */
    public function getAction(string $id)
    {
        return $this->view(
            $this->findBrandById($id)
        );
    }

    /**
     * Expose all Brands
     *
     * @SWG\Get(
     *     summary="Get all Brands",
     *     produces={"application/json"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return all the Brands",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Brand::class))
     *     )
     * )
     *
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        if ($this->isGranted("ROLE_AMBASSADOR"))
            return $this->view(
                $this->brandRepository->findAll()
            );
        $user = $this->getUser();
        $brands = $this->brandRepository->findByUserOrValidate($user);
        return $this->view($brands);
    }

    /**
     * Update a Brand.
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
     *     description="The Brand based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="The full JSON Brand",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     ),
     *     description="The JSon Brand"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     *    )
     * )
     *
     * @IsGranted("ROLE_AMBASSADOR")
     * @param Request $request
     * @param string $id of the Brand to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function putAction(Request $request, string $id)
    {
        $existingBrand = $this->findBrandById($id);
        $form = $this->createForm(BrandType::class, $existingBrand);
        $validate = $existingBrand->getValidate();
        $form->submit($request->request->all());

        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($existingBrand->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Update a part of a Brand
     *
     * All missing attribute will not be update.
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
     *     description="The Brand based on ID is not found"
     *    ),
     *    @SWG\Parameter(
     *     name="A part of a JSON Brand",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *       ref=@Model(type=Brand::class)
     *     ),
     *     description="A part of a JSon Brand"
     *    ),
     *    @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     *    )
     * )
     *
     * @IsGranted("ROLE_AMBASSADOR")
     * @param Request $request
     * @param string $id of the Brand to update
     * @return \FOS\RestBundle\View\View|JsonResponse
     */
    public function patchAction(Request $request, string $id)
    {
        $existingBrand = $this->findBrandById($id);

        $form = $this->createForm(BrandType::class, $existingBrand);
        $validate = $existingBrand->getValidate();
        $data = json_decode($request->getContent());
        
        $form->submit($data, false);

        if (false === $form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $this->formErrorSerializer->normalize($form),
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($existingBrand->getValidate() !== $validate) {
            $this->denyAccessUnlessGranted("ROLE_AMBASSADOR");
        }
        $this->entityManager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a Brand with the id
     *
     *
     * You should know what your are doing ! Cannot be reverse.
     *
     * @SWG\Delete()
     * @SWG\Response(
     *     response=204,
     *     description="The brand is correctly delete",
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="The brand based on ID is not found"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The ID used to find the Brand"
     * )
     * 
     * @param string $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(string $id)
    {
        $brand = $this->findBrandById($id);
        if ($brand->getCreatedBy() !== $this->getUser())
            $this->denyAccessUnlessGranted(
                "ROLE_ADMIN",
                null,
                "This brand don't belong to you and your are not an admin."
            );

        if (
            !$brand->getValidate()
            || count($brand->getEquipments()) < 1
        ) {
            $this->entityManager->remove($brand);
            $this->entityManager->flush();

            return $this->view(
                null,
                Response::HTTP_NO_CONTENT
            );
        }
        if (count($brand->getEquipments()) > 1) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => 'Too many other equipments use this brand.'
                ],
                JsonResponse::HTTP_PRECONDITION_FAILED
            );
        }
        return new JsonResponse(
            [
                'status' => 'error',
                'errors' => 'This brand don\'t belong to you'
            ],
            JsonResponse::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * @param string $id
     *
     * @return Brand
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findBrandById(string $id)
    {
        $existingBrand = $this->brandRepository->find($id);

        if (null === $existingBrand) {
            throw new NotFoundHttpException();
        }

        return $existingBrand;
    }

        /**
     * @param Request $request
     *
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function findUserByRequest(Request $request)
    {
        $user = $this->entityManager->find(
            User::class,
            $request->attributes->get('userid'));
        if($user == null)
            throw new NotFoundHttpException();
        return $user;
    }
}
