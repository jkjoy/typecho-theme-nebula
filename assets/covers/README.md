# 本地随机封面

将多张封面图片直接放在此目录，然后在主题设置中开启“本地随机封面”。默认关闭。

支持 JPG、JPEG、PNG、GIF、WebP、AVIF 和 SVG，扩展名不区分大小写。不读取子目录，非图片文件不参与随机。

开启后，文章列表优先使用随机图片；目录中没有可用图片时自动使用原有封面逻辑。

## 内置封面

已附带 6 张 NASA 公开图库素材，统一裁切为 1280×720 的 WebP 图片，适配主题的 16:9 文章卡片。图片在本地加载，不依赖外部图片服务。

| 文件 | 内容 | 原图来源与署名 |
| --- | --- | --- |
| `nebula-carina.webp` | 船底座星云“宇宙悬崖” | [NASA 图库](https://images.nasa.gov/details/carina_nebula)；NASA、ESA、CSA、STScI |
| `galaxy-andromeda.webp` | 仙女座星系 | [NASA 图库](https://images.nasa.gov/details/PIA04921)；NASA/JPL/California Institute of Technology |
| `nebula-orion.webp` | 猎户座星云 | [NASA 图库](https://images.nasa.gov/details/PIA13959)；NASA/ESA/JPL-Caltech/IRAM |
| `nebula-helix.webp` | 螺旋星云 | [NASA 图库](https://images.nasa.gov/details/PIA09178)；NASA/JPL-Caltech/Univ. of Ariz. |
| `nebula-heart-and-soul.webp` | 心脏与灵魂星云 | [NASA 图库](https://images.nasa.gov/details/PIA13112)；NASA/JPL-Caltech/UCLA |
| `earth-aurora.webp` | 从国际空间站拍摄的地球与极光 | [NASA 图库](https://images.nasa.gov/details/iss058e005282)；NASA |

以上图片已做裁切和格式转换；可自行添加或删除图片来调整随机封面集合。
