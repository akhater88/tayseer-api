<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class ProfileSwagger
{
    #[OA\Get(
        path: "/api/v1/me",
        tags: ["Profile"],
        summary: "Get current user profile",
        description: "الحصول على بيانات المستخدم الحالي",
        operationId: "getMe",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "User profile - الملف الشخصي",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", ref: "#/components/schemas/UserFull")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function getMe() {}

    #[OA\Put(
        path: "/api/v1/me",
        tags: ["Profile"],
        summary: "Update current user profile",
        description: "تحديث بيانات الملف الشخصي",
        operationId: "updateMe",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "about_me", type: "string", description: "About me text - نبذة عني"),
                    new OA\Property(property: "partner_preferences", type: "string", description: "Partner preferences - تفضيلات الشريك"),
                    new OA\Property(property: "height_cm", type: "integer", minimum: 140, maximum: 210),
                    new OA\Property(property: "weight_kg", type: "integer", minimum: 40, maximum: 150),
                    new OA\Property(property: "job_title", type: "string", maxLength: 100),
                    new OA\Property(property: "work_field_id", type: "integer")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile updated - تم تحديث الملف الشخصي",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم تحديث الملف الشخصي"),
                        new OA\Property(property: "data", ref: "#/components/schemas/UserFull")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation errors - أخطاء التحقق")
        ]
    )]
    public function updateMe() {}

    #[OA\Post(
        path: "/api/v1/me/photos",
        tags: ["Profile"],
        summary: "Upload profile photo",
        description: "رفع صورة شخصية - Max 5 photos, 5MB each - 5 صور كحد أقصى، 5 ميجابايت لكل صورة",
        operationId: "uploadPhoto",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "photo", type: "string", format: "binary", description: "JPG/PNG, max 5MB"),
                        new OA\Property(property: "is_primary", type: "boolean", example: false)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Photo uploaded - تم رفع الصورة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم رفع الصورة بنجاح"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Photo")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Maximum photos reached or invalid file - تم الوصول للحد الأقصى أو ملف غير صالح")
        ]
    )]
    public function uploadPhoto() {}

    #[OA\Delete(
        path: "/api/v1/me/photos/{photoId}",
        tags: ["Profile"],
        summary: "Delete a photo",
        description: "حذف صورة",
        operationId: "deletePhoto",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "photoId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Photo deleted - تم حذف الصورة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم حذف الصورة")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Photo not found - الصورة غير موجودة")
        ]
    )]
    public function deletePhoto() {}

    #[OA\Put(
        path: "/api/v1/me/photos/{photoId}/primary",
        tags: ["Profile"],
        summary: "Set photo as primary",
        description: "تعيين الصورة كصورة رئيسية",
        operationId: "setPrimaryPhoto",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "photoId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Primary photo updated - تم تحديث الصورة الرئيسية",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم تعيين الصورة الرئيسية")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Photo not found - الصورة غير موجودة")
        ]
    )]
    public function setPrimaryPhoto() {}
}
