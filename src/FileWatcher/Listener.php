<?php


namespace Tohidplus\Translation\FileWatcher;


use ElementaryFramework\FireFS\Events\FileSystemEvent;
use ElementaryFramework\FireFS\Listener\IFileSystemListener;
use Tohidplus\Translation\CLIPrinter;
use Tohidplus\Translation\Facades\VueTranslation;

class Listener implements IFileSystemListener
{

    /**
     * Action called on any event.
     *
     * @param FileSystemEvent $event The raised event.
     *
     * @return boolean true to propagate the event, false otherwise.
     */
    function onAny(FileSystemEvent $event): bool
    {
        try {
            VueTranslation::compile();
        } catch (\Throwable $exception) {
            CLIPrinter::print("Error on [{$exception->getLine()}] - [{$exception->getFile()}]", CLIPrinter::FOREGROUND_WHITE, CLIPrinter::BACKGROUND_RED);
            CLIPrinter::print($exception->getMessage());
        }
        return false;
    }

    /**
     * Action called when a "create" event occurs on
     * the file system.
     *
     * @param FileSystemEvent $event The raised event.
     *
     * @return void
     */
    function onCreated(FileSystemEvent $event)
    {
        // TODO: Implement onCreated() method.
    }

    /**
     * Action called when a "modify" event occurs on
     * the file system.
     *
     * @param FileSystemEvent $event The raised event.
     *
     * @return void
     */
    function onModified(FileSystemEvent $event)
    {

    }

    /**
     * Action called when a "delete" event occurs on
     * the file system.
     *
     * @param FileSystemEvent $event The raised event.
     *
     * @return void
     */
    function onDeleted(FileSystemEvent $event)
    {
        // TODO: Implement onDeleted() method.
    }
}
