<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class DiscoverSwagger
{
    #[OA\Get(
        path: "/api/v1/discover",
        tags: ["Discovery"],
        summary: "Discover potential matches",
        description: "استكشاف الملفات الشخصية - Returns profiles of opposite gender",
        operationId: "discover",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                schema: new OA\Schema(type: "integer", default: 20, maximum: 50)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Paginated profiles - الملفات الشخصية مع ترقيم الصفحات",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/ProfileCard")
                        ),
                        new OA\Property(property: "meta", ref: "#/components/schemas/Pagination")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function discover() {}

    #[OA\Get(
        path: "/api/v1/discover/recommendations",
        tags: ["Discovery"],
        summary: "Get personalized recommendations",
        description: "توصيات مخصصة - Based on preferences and compatibility",
        operationId: "recommendations",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Recommended profiles - الملفات الشخصية الموصى بها",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/ProfileCard")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function recommendations() {}

    #[OA\Get(
        path: "/api/v1/search",
        tags: ["Search"],
        summary: "Advanced profile search",
        description: "البحث المتقدم",
        operationId: "search",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "country_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "city_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "nationality_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(
                name: "age_min",
                in: "query",
                schema: new OA\Schema(type: "integer", minimum: 18, maximum: 80)
            ),
            new OA\Parameter(
                name: "age_max",
                in: "query",
                schema: new OA\Schema(type: "integer", minimum: 18, maximum: 80)
            ),
            new OA\Parameter(
                name: "marital_status",
                in: "query",
                schema: new OA\Schema(type: "string", enum: ["single", "divorced", "widowed"])
            ),
            new OA\Parameter(name: "religious_level", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(
                name: "hijab_type",
                in: "query",
                description: "For female search - للبحث عن الإناث",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "beard_type",
                in: "query",
                description: "For male search - للبحث عن الذكور",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(name: "has_children", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "education_level", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Search results - نتائج البحث",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/ProfileCard")
                        ),
                        new OA\Property(property: "meta", ref: "#/components/schemas/Pagination")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function search() {}

    #[OA\Get(
        path: "/api/v1/profiles/{slug}",
        tags: ["Search"],
        summary: "View single profile",
        description: "عرض ملف شخصي",
        operationId: "showProfile",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "slug",
                in: "path",
                required: true,
                description: "User's unique slug - المعرف الفريد للمستخدم",
                schema: new OA\Schema(type: "string", example: "ahmed-m4k9")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Full profile - الملف الشخصي الكامل",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", ref: "#/components/schemas/ProfileFull")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Profile not found - الملف الشخصي غير موجود")
        ]
    )]
    public function showProfile() {}
}
