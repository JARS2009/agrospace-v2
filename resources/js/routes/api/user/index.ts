import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
export const landArea = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: landArea.url(options),
    method: 'get',
})

landArea.definition = {
    methods: ["get","head"],
    url: '/api/user/land-area',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
landArea.url = (options?: RouteQueryOptions) => {
    return landArea.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
landArea.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: landArea.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
landArea.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: landArea.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
    const landAreaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: landArea.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
        landAreaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: landArea.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Settings\ProfileController::landArea
 * @see app/Http/Controllers/Settings/ProfileController.php:46
 * @route '/api/user/land-area'
 */
        landAreaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: landArea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    landArea.form = landAreaForm
const user = {
    landArea: Object.assign(landArea, landArea),
}

export default user