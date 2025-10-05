import Auth from './Auth'
import PlayerProfileController from './PlayerProfileController'
import Settings from './Settings'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
PlayerProfileController: Object.assign(PlayerProfileController, PlayerProfileController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers