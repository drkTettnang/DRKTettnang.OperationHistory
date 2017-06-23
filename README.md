# DRKTettnang.OperationHistory

## Installation

- put this folder to `NEOS/Packages/Application/`
- rescan packages with `./flow flow:package:rescan`
- add [Snippet 1](#snippet-1) to the beginning of `NEOS/Configuration/Route.yaml`
- execute database update with `./flow doctrine:update`
- activate user role `Moderator` for desired users

## Troubleshooting

- On php 7 add `\TYPO3\Flow\Mvc\View\ViewInterface` to `Controller/OperationController.php line 466`

## Snippets
### Snippet 1
```
-
  name: 'OperationHistory'
  uriPattern: '<OperationHistoryPluginSubroutes>'
  subRoutes:
    OperationHistoryPluginSubroutes:
      package: 'DRKTettnang.OperationHistory'
```
