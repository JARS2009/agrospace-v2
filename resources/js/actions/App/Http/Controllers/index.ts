import Auth from './Auth'
import DashboardController from './DashboardController'
import PlayerProfileController from './PlayerProfileController'
import Settings from './Settings'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
DashboardController: Object.assign(DashboardController, DashboardController),
PlayerProfileController: Object.assign(PlayerProfileController, PlayerProfileController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers