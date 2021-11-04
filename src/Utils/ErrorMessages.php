<?php

namespace ForestAdmin\LaravelForestAdmin\Utils;

/**
 * Class ErrorMessages
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ErrorMessages
{
    public const AUTH_SECRET_MISSING = 'Your Forest authSecret seems to be missing. Can you check that you properly set a Forest authSecret in the Forest initializer?';

    public const SECRET_AND_RENDERINGID_INCONSISTENT = 'Cannot retrieve the project you\'re trying to unlock. The envSecret and renderingId seems to be missing or inconsistent.';

    public const SERVER_DOWN = 'Cannot retrieve the data from the Forest server. Forest API seems to be down right now.';

    public const SECRET_NOT_FOUND = 'Cannot retrieve the data from the Forest server. Can you check that you properly copied the Forest envSecret in the Liana initializer?';

    public const UNEXPECTED = 'Cannot retrieve the data from the Forest server. An error occured in Forest API';

    public const INVALID_STATE_MISSING = 'Invalid response from the authentication server: the state parameter is missing';

    public const INVALID_STATE_FORMAT = 'Invalid response from the authentication server: the state parameter is not at the right format';

    public const INVALID_STATE_RENDERING_ID = 'Invalid response from the authentication server: the state does not contain a renderingId';

    public const MISSING_RENDERING_ID = 'Authentication request must contain a renderingId';

    public const INVALID_RENDERING_ID = 'The parameter renderingId is not valid';

    public const REGISTRATION_FAILED = 'The registration to the authentication API failed, response: ';

    public const OIDC_CONFIGURATION_RETRIEVAL_FAILED = 'Failed to retrieve the provider\'s configuration.';

    public const TWO_FACTOR_AUTHENTICATION_REQUIRED = 'TwoFactorAuthenticationRequiredForbiddenError';

    public const AUTHORIZATION_FAILED = 'Error while authorizing the user on Forest Admin';
}
