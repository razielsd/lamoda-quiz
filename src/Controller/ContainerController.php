<?php

declare(strict_types=1);

namespace App\Controller;

use App\Storage\Exception;
use App\Storage\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContainerController extends AbstractController
{
    /**
     * Получение списка контейнеров
     *
     * @param Request $request
     * @return Response
     */
    public function actionGetContainerSlice(Request $request)
    {
        $limit = (int) $request->get('limit', 1);
        $offset = (int) $request->get('offset', 0);
        try {
            $data = (new Storage())->getContainerSlice($limit, $offset);
            $containerList = [];
            foreach ($data as $row) {
                $container = json_decode($row['container'], true);
                $container['id'] = $row['id'];
                $containerList[$row['id']] = $container;
            }
        } catch (Exception $e) {
            return $this->createError(
                'Error getting container: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return new Response(
            json_encode($containerList)
        );
    }

    /**
     * Получение списка контейнеров
     *
     * @param Request $request
     * @return Response
     */
    public function actionGetContainerById(Request $request)
    {
        $id = (int) $request->get('id', 1);

        try {
            $row = (new Storage())->getContainerById($id);
            if (!$row) {
                return $this->createError(
                    'Container not found', Response::HTTP_NOT_FOUND
                );
            }
            $container = json_decode($row['container'], true);
            $container['id'] = $row['id'];
        } catch (Exception $e) {
            return $this->createError(
                'Error getting container: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return new Response(
            json_encode([$id => $container])
        );
    }

    /**
     * Создание контейнера
     *
     * @param Request $request
     * @return Response
     */
    public function actionAddContainer(Request $request)
    {
        $container = $request->getContent();
        $container = json_decode($container, true);

        if ($container === false) {
            return $this->createError('Bad json format');
        }

        // здесь надо проверить json-схему
        // https://json-schema.org/
        // какую либу лучше использовать сейчас незнаю, мы использовали https://github.com/justinrainbow/json-schema
        // но в ней есть проблемы со вложеностью элементов и давно не обновлялась

        try {
            $id = (new Storage())->storeContainer($container);
        } catch (Exception $e) {
            return $this->createError(
                'Error saving container', Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return new Response(
            json_encode(['id' => $id])
        );
    }

    /**
     * @param $message
     * @param int $code
     * @return Response
     */
    protected function createError($message, $code = Response::HTTP_BAD_REQUEST) {
        return new Response(
            json_encode([
            'status' => 'error',
            'message' => $message
            ]),
            $code
        );
    }

}