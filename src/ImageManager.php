<?php

namespace Joselfonseca\ImageManager;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Joselfonseca\ImageManager\Commands\UploadFile\UploadFileCommand;
use Laracasts\Commander\CommanderTrait;
use Joselfonseca\ImageManager\Interfaces\ImageRepositoryInterface;
use Joselfonseca\ImageManager\Commands\RenderFile\RenderFileCommand;
use Illuminate\Http\Request;


/**
 * Class ImageManager
 * @package Joselfonseca\ImageManager
 */
class ImageManager
{

    use CommanderTrait;

    /**
     * @var ImageRepositoryInterface
     */
    private $ImageRepository;

    /**
     * @var Request
     */
    private $request;


    /**
     * @param Request $request
     * @param ImageRepositoryInterface $imageRepository
     */
    public function __construct(Request $request, ImageRepositoryInterface $imageRepository)
    {
        $this->ImageRepository = $imageRepository;
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function doUpload($fromManager = 1)
    {
        return $this->execute(UploadFileCommand::class, ['file' => \Input::file('file'), 'fromManager' => $fromManager]);
    }

    /**
     * @param $id
     * @param null $width
     * @param null $height
     * @param bool $canvas
     * @return mixed
     */
    public function resize($id, $width = null, $height = null, $canvas = true)
    {
        return $this->execute(RenderFileCommand::class,
            ['id' => $id, 'width' => $width, 'height' => $height, 'canvas' => (bool)$canvas]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function imageInfo($id)
    {
        return $this->ImageRepository->getFileModel($id);
    }

    /**
     * @param $params
     * @return string
     */
    public static function getField($params)
    {
        $text = ($params['text']) ? $params['text'] : 'Select File';
        $class = ($params['class']) ? $params['class'] : 'btn btn-default';
        $field_name = (isset($params['field_name'])) ? $params['field_name'] : 'image';
        $default = (isset($params['default'])) ? $params['default'] : \Input::old($params['field_name']);
        if (!empty($default)) {
            $image = '<img src="' . route('showthumb', $default) . '" class="imageManagerImage" />';
        } else {
            $image = '<img src="" style="display:none" class="imageManagerImage" />';
        }

        return '<div class="ImageManager">'
        . $image . '<br /><br />'
        . '<button class="fileManager ' . $class . '" type="Button" data-url="' . route('ImageManager') . '" data-multi="false">' . $text . '</button>'
        . \Form::hidden($field_name, $default, ['class' => 'inputFile'])
        . '</div>';
    }

    /**
     * @param $params
     * @return string
     */
    public static function getMultiField($params)
    {
        $params['field_name'] = (isset($params['field_name'])) ? $params['field_name'] : 'images';
        $params['default'] = (isset($params['default'])) ? $params['default'] : [];
        $repository = app('Joselfonseca\ImageManager\Interfaces\ImageRepositoryInterface');
        $params['default'] = array_map(function($file) use($repository){
            try{
                return $repository->getFileModel($file);
            }catch (ModelNotFoundException $e){
                return null;
            }
        }, $params['default']);
        return view('image-manager::image_manager_multi')->with('params', $params)->render();
    }

}
