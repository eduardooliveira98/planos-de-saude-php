-- Trigger: BIpassagens

-- DROP TRIGGER "BIpassagens" ON public.passagens;

CREATE TRIGGER "BIpassagens"
    BEFORE INSERT
    ON public.passagens
    FOR EACH ROW
    EXECUTE PROCEDURE public."passagensBI"();

COMMENT ON TRIGGER "BIpassagens" ON public.passagens
    IS 'A quantidade de passagens vendidas para uma viagem é limitada à capacidade de acentos no ônibus que faz a viagem.' 

==================================================================================================

DECLARE 

	vendidas INTEGER = 0;
	lugares INTEGER = 1;

BEGIN

	SELECT passvend INTO vendidas FROM (SELECT * FROM (SELECT onibus.cpveiculo, onibus.qtcapacidade, COUNT(passagens.cppassagem) AS passvend, passagens.ceviagem FROM passagens 
	JOIN onibus ON onibus.cpveiculo = passagens.ceonibus GROUP BY passagens.ceviagem, onibus.qtcapacidade, onibus.cpveiculo) AS B WHERE ceviagem = new.ceviagem AND cpveiculo = new.ceonibus) AS C;

	SELECT qtcapacidade INTO lugares FROM (SELECT * FROM (SELECT onibus.cpveiculo, onibus.qtcapacidade, COUNT(passagens.cppassagem) AS passvend, passagens.ceviagem FROM passagens 
	JOIN onibus ON onibus.cpveiculo = passagens.ceonibus GROUP BY passagens.ceviagem, onibus.qtcapacidade, onibus.cpveiculo) AS D WHERE ceviagem = new.ceviagem AND cpveiculo = new.ceonibus) AS E;

	IF lugares <= vendidas THEN
		RAISE EXCEPTION 'A quantidade de passagens para esta viagem já foi esgotada!';
	ELSE 
		RETURN new;
	END IF;

END;