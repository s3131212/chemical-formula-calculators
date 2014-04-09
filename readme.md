介面更新 By Yuxin
--------------------
* 將整體介面使用 Flat 風格優化。
* 整理程式碼。
* 顏色隨機挑選。

化學式與莫耳數計算機
====================
此程式是為了解化學中關於原子量、分子量與莫耳題目用的，採用 PHP 製作。莫耳與原子量、分子量是進入化學首先必須學會的，此程式是為了快速計算答案而設計的，可用於驗算，可以快速算出一定重量的莫耳數，含有個元素的重量，單一分子的重量等等，內容十分完整。

關於作者
--------------------
此程式由[Allen](http://s3131212.com/)製作

安裝方法
--------------------
將檔案放到 PHP 伺服器後即可執行

資源貢獻
--------------------
此軟體之介面使用 [Bootstrap](http://getbootstrap.com/) 設計

版權宣告
--------------------
此程式為開放原始碼，採用 MIT 授權發行，只要遵守 MIT 條款，您可以任意使用此軟體，詳細內容請見LICENSE檔案


已知問題
--------------------
* 在效能較差的伺服器或是同時太多人使用，可能導致解答載入緩慢
* 當係數超過百位（通常是亂輸入）或元素名稱有三位以上（Uut等等，幾乎不可能用到）時，可能因為化學式無法被正確拆解而發生 CPU 超載或是 PHP 被強制停止
