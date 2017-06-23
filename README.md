# DRKTettnang.OperationHistory

## Installation

- put this folder to `NEOS/Packages/Application/`
- rescan packages with `./flow flow:package:rescan`
- add [Snippet 1](#snippet-1) to the beginning of `NEOS/Configuration/Route.yaml`
- execute database update with `./flow doctrine:update`
- activate user role `Moderator` for desired users

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

## Screenshots

---

![](https://raw.githubusercontent.com/drkTettnang/DRKTettnang.OperationHistory/master/Documentation/screenshot-1.png)

---

![](https://raw.githubusercontent.com/drkTettnang/DRKTettnang.OperationHistory/master/Documentation/screenshot-2.png)

---

![](https://raw.githubusercontent.com/drkTettnang/DRKTettnang.OperationHistory/master/Documentation/screenshot-3.png)

---
