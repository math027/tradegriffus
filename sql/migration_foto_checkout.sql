-- Se você JÁ executou a migration anterior, rode apenas isto:
USE griffu80_trade;
ALTER TABLE visitas ADD COLUMN foto_checkout VARCHAR(255) AFTER fotos_trabalho;
