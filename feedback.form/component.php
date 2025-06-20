<?php
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

$request = Context::getCurrent()->getRequest();
$errors = [];

if (
    $request->isPost() &&
    $request->getPost("submit") === "Y" &&
    $request->isAjaxRequest()
) {
    define("PUBLIC_AJAX_MODE", true);
    $GLOBALS["APPLICATION"]->RestartBuffer();
    header('Content-Type: application/json');

    $name = trim(strip_tags($request->getPost("name")));
    $email = trim(strip_tags($request->getPost("email")));
    $message = strip_tags($request->getPost("message"));

    if ($name === "") {
        $errors[] = "Имя обязательно";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email";
    }
    if ($message === "") {
        $errors[] = "Текст отзыва обязателен";
    }

    if (empty($errors)) {
        if (Loader::includeModule("iblock")) {
            $el = new CIBlockElement;

            $arLoadProductArray = [
                "IBLOCK_ID" => 5,
                "NAME" => $name,
                "ACTIVE" => "Y",
                "PREVIEW_TEXT" => $message,
                "PROPERTY_VALUES" => [
                    "EMAIL" => $email
                ]
            ];

            if ($ID = $el->Add($arLoadProductArray)) {
                $lastItems = [];
                $res = CIBlockElement::GetList(
                    ["ID" => "DESC"],
                    ["IBLOCK_ID" => 5, "ACTIVE" => "Y"],
                    false,
                    ["nTopCount" => 3],
                    ["ID", "NAME", "PREVIEW_TEXT"]
                );
                while ($ob = $res->GetNext()) {
                    $lastItems[] = [
                        "NAME" => $ob["NAME"],
                        "PREVIEW_TEXT" => $ob["PREVIEW_TEXT"]
                    ];
                }

                echo json_encode([
                    "status" => "success",
                    "message" => "Спасибо за отзыв!",
                    "name" => htmlspecialcharsbx($name),
                    "user_message" => nl2br(htmlspecialcharsbx($message)),
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Ошибка сохранения"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Модуль iblock не найден"]);
        }
    } else {
        echo json_encode(["status" => "error", "errors" => $errors]);
    }

    CMain::FinalActions();
    die();
}

$arResult["ITEMS"] = [];
if (Loader::includeModule("iblock")) {
    $res = CIBlockElement::GetList(
        ["ID" => "DESC"],
        ["IBLOCK_ID" => 5, "ACTIVE" => "Y"],
        false,
        ["nTopCount" => 3],
        ["ID", "NAME", "PREVIEW_TEXT"]
    );
    while ($ob = $res->GetNext()) {
        $arResult["ITEMS"][] = $ob;
    }
}

$this->IncludeComponentTemplate();
